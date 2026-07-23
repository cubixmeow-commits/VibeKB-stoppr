<?php

declare(strict_types=1);

/**
 * Focused test for explainable-diagram topology parsing and validation.
 *
 * There is no third-party test framework in this repo (no Composer, no PHPUnit),
 * so this is a self-contained assertion script in the same spirit as
 * tools/validate.php. It copies the real `.vibekb/` into a temp directory,
 * injects a deliberately malformed topology, loads it through the same Content
 * loader the guide uses, and asserts that:
 *
 *   1. loading does not crash (malformed topology is reported, not fatal); and
 *   2. each specific contract violation is surfaced as an issue.
 *
 * Works in self-hosted VibeKB and in installed target repositories by
 * discovering a carrier diagram and a known-good topology from the local model.
 *
 * Exits non-zero if any assertion fails, so it can gate CI.
 *
 * Usage: php .vibekb/runtime/tools/test-topology.php
 */

$runtimeRoot = dirname(__DIR__);
require_once $runtimeRoot . '/guide/lib/workspace.php';
require_once $runtimeRoot . '/guide/lib/helpers.php';
require_once $runtimeRoot . '/guide/lib/Content.php';

// Content root is the active `.vibekb`; its parent is the project root.
$contentRoot = vibekb_locate_content_root($runtimeRoot) ?? ($runtimeRoot . '/.vibekb');
$repoRoot = dirname($contentRoot);

$tmp = sys_get_temp_dir() . '/vibekb-topo-test-' . bin2hex(random_bytes(4));

/** Recursively copy a directory. */
$copy = static function (string $src, string $dst) use (&$copy): void {
    @mkdir($dst, 0775, true);
    foreach (scandir($src) ?: [] as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }
        $s = $src . '/' . $entry;
        $d = $dst . '/' . $entry;
        is_dir($s) ? $copy($s, $d) : copy($s, $d);
    }
};
$copy($repoRoot . '/.vibekb', $tmp);

$recordsDir = $tmp . '/diagrams/records';
$topologyDir = $tmp . '/diagrams/topology';
if (!is_dir($recordsDir)) {
    fwrite(STDERR, "FAIL: no diagrams/records/ in .vibekb — cannot run topology test\n");
    exit(1);
}

/**
 * Prefer a picture-only diagram (no topology: line) as the malformed carrier.
 * Prefer self-maintenance-loop when present (self-hosted fixture). If every
 * record already has a topology, strip topology from the first record.
 *
 * @return array{0:string,1:string} [absolute path, svg basename]
 */
$pickCarrier = static function (string $recordsDir): array {
    $preferred = $recordsDir . '/self-maintenance-loop.md';
    $candidates = [];
    foreach (scandir($recordsDir) ?: [] as $entry) {
        if (!str_ends_with($entry, '.md')) {
            continue;
        }
        $path = $recordsDir . '/' . $entry;
        $body = (string) file_get_contents($path);
        if (!preg_match('/^svg:\s*(\S+)/m', $body, $m)) {
            continue;
        }
        $svg = $m[1];
        $hasTopology = (bool) preg_match('/^topology:\s*\S+/m', $body);
        if ($path === $preferred || !$hasTopology) {
            return [$path, $svg];
        }
        $candidates[] = [$path, $svg, $hasTopology];
    }
    if ($candidates === []) {
        fwrite(STDERR, "FAIL: no diagram records with an svg: field\n");
        exit(1);
    }
    return [$candidates[0][0], $candidates[0][1]];
};

[$recordPath, $svgName] = $pickCarrier($recordsDir);
$carrierId = basename($recordPath, '.md');
$record = (string) file_get_contents($recordPath);
// Ensure the carrier points at broken.json (replace or insert topology line).
if (preg_match('/^topology:\s*\S+/m', $record)) {
    $record = preg_replace(
        '/^topology:\s*\S+$/m',
        'topology: broken.json',
        $record,
        1,
    );
} else {
    $record = preg_replace(
        '/^svg:\s*' . preg_quote($svgName, '/') . '$/m',
        "svg: {$svgName}\ntopology: broken.json",
        $record,
        1,
    );
}
file_put_contents($recordPath, $record);

// A topology that violates as many rules as possible in one file.
$broken = <<<'JSON'
{
  "version": 9,
  "nodes": [
    {
      "id": "a",
      "title": "Node A",
      "purpose": "",
      "verification": "bogus-state",
      "files": [{ "path": "app/x.php", "role": "weird-role" }]
    },
    { "id": "b", "title": "Node B", "purpose": "A valid node." },
    { "id": "b", "title": "Duplicate B", "purpose": "Second entry with the same id." }
  ],
  "edges": [
    {
      "id": "e1",
      "from": "a",
      "to": "missing-node",
      "mechanism": "relates-to",
      "explanation": ""
    }
  ]
}
JSON;
@mkdir($topologyDir, 0775, true);
file_put_contents($topologyDir . '/broken.json', $broken);

// Load — must not throw.
$content = new Content($tmp);
try {
    $content->load();
} catch (Throwable $e) {
    fwrite(STDERR, "FAIL: loading crashed on malformed topology: " . $e->getMessage() . "\n");
    exit(1);
}

$messages = array_map(fn ($i) => $i['message'], $content->issues());
$haystack = implode("\n", $messages);

$expect = [
    'unsupported schema version',
    'duplicate node id: b',
    "node 'a' is missing a purpose",
    "node 'a' has unknown verification: bogus-state",
    "shows file 'app/x.php' without a reason",
    "edge 'e1' has an unresolved target node",
    "edge 'e1' uses an out-of-vocabulary mechanism: relates-to",
    "edge 'e1' is missing a one-sentence explanation",
    "edge 'e1' is missing a verification state",
    "node 'a' has no data-vibekb-node marker in the SVG",
    "edge 'e1' has no data-vibekb-edge marker in the SVG",
];

$failures = 0;
foreach ($expect as $needle) {
    $ok = str_contains($haystack, $needle);
    echo ($ok ? '  ok   ' : '  FAIL ') . "expected diagnostic: {$needle}\n";
    if (!$ok) {
        $failures++;
    }
}

// Pick a well-formed topology from the real model (not broken.json and
// not the carrier we deliberately pointed at the malformed fixture).
$goodId = null;
$goodNodes = 0;
$goodEdges = 0;
foreach (scandir($repoRoot . '/.vibekb/diagrams/topology') ?: [] as $entry) {
    if (!str_ends_with($entry, '.json') || $entry === 'broken.json') {
        continue;
    }
    $id = basename($entry, '.json');
    if ($id === $carrierId) {
        continue;
    }
    // Prefer the self-hosted fixture when present.
    if ($id === 'content-load-flow' || $goodId === null) {
        $data = json_decode(
            (string) file_get_contents(
                $repoRoot . '/.vibekb/diagrams/topology/' . $entry,
            ),
            true,
        );
        if (!is_array($data)) {
            continue;
        }
        $goodId = $id;
        $goodNodes = count($data['nodes'] ?? []);
        $goodEdges = count($data['edges'] ?? []);
        if ($id === 'content-load-flow') {
            break;
        }
    }
}

if ($goodId === null) {
    echo "  FAIL no well-formed topology found in .vibekb/diagrams/topology/\n";
    $failures++;
} else {
    $rf = $content->resolvedTopology($goodId);
    if ($rf === null
        || count($rf['nodes']) !== $goodNodes
        || count($rf['edges']) !== $goodEdges
    ) {
        echo "  FAIL good topology '{$goodId}' did not resolve as expected\n";
        $failures++;
    } else {
        echo "  ok   good topology '{$goodId}' resolves"
            . " ({$goodNodes} nodes, {$goodEdges} edges)\n";
    }
}

// Clean up.
$rrmdir = static function (string $dir) use (&$rrmdir): void {
    foreach (scandir($dir) ?: [] as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }
        $p = $dir . '/' . $entry;
        is_dir($p) ? $rrmdir($p) : @unlink($p);
    }
    @rmdir($dir);
};
$rrmdir($tmp);

echo $failures === 0 ? "OK\n" : "FAILED ({$failures})\n";
exit($failures === 0 ? 0 : 1);
