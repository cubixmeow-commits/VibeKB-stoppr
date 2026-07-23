<?php

declare(strict_types=1);

/**
 * Headless VibeKB content validator (CI-friendly).
 *
 * Loads the `.vibekb/` model through the same loader the guide uses, then adds
 * a few checks that only matter for a publishable/generated model. Prints a
 * human-readable report and exits non-zero if there are any errors, so it can
 * gate commits and CI.
 *
 * Usage:
 *   php tools/validate.php                         # validate the active .vibekb/
 *   php tools/validate.php examples/sousmeow/.vibekb   # validate a bundled example
 *
 * Checks (loader): duplicate ids; functionality missing required fields;
 * invalid statuses/verification/safety; unresolved depends_on / related_memory /
 * file & memory back-links; diagrams missing fields; diagram SVG missing,
 * malformed, or lacking accessible <title>/<desc>; unresolved diagram links.
 *
 * Checks (here): provenance fields present for a publishable model; area/record
 * totals reconcile; and — if a generated /docs snapshot exists — its search
 * index only points at pages that actually exist.
 */

$runtimeRoot = dirname(__DIR__);
require_once $runtimeRoot . '/guide/lib/workspace.php';
require_once $runtimeRoot . '/guide/lib/helpers.php';
require_once $runtimeRoot . '/guide/lib/Content.php';
require_once $runtimeRoot . '/guide/lib/Provenance.php';
require_once $runtimeRoot . '/guide/lib/UrlStrategy.php';
require_once $runtimeRoot . '/guide/lib/search.php';

// Project root: parent of the located `.vibekb` (equals $runtimeRoot only in the
// self-hosted layout). Argument paths and the snapshot search index resolve here.
$contentRootDefault = vibekb_locate_content_root($runtimeRoot) ?? ($runtimeRoot . '/.vibekb');
$repoRoot = dirname($contentRootDefault);

// Optional first argument: a content root to validate instead of the active
// .vibekb/ (used to validate bundled example models). Relative paths resolve
// against the repository root; the path must end in a `.vibekb` directory.
$contentRoot = $repoRoot . '/.vibekb';
$argPath = $argv[1] ?? '';
if ($argPath !== '') {
    $candidate = (str_starts_with($argPath, '/') ? $argPath : $repoRoot . '/' . $argPath);
    $candidate = rtrim($candidate, '/');
    if (basename($candidate) !== '.vibekb' || !is_dir($candidate)) {
        fwrite(STDERR, "Not a .vibekb content directory: {$argPath}\n");
        exit(2);
    }
    $contentRoot = $candidate;
}

$content = new Content($contentRoot);
$content->load();

echo 'Validating: ' . $contentRoot . "\n";

$errors = [];
$warnings = [];
foreach ($content->issues() as $issue) {
    if ($issue['level'] === 'error') {
        $errors[] = $issue['message'];
    } else {
        $warnings[] = $issue['message'];
    }
}

// ---- provenance completeness ---------------------------------------------
$p = provenance_data($content->manifest());
foreach (['source_repository', 'source_commit'] as $field) {
    if (($p[$field] ?? '') === '') {
        $warnings[] = "Provenance is missing '{$field}' — a publishable snapshot should record it.";
    }
}

// ---- totals reconcile (unit-labelled counts) ------------------------------
$records = count($content->allFunctionality());
$areas = count($content->functionalityGroups());
$statusTotal = array_sum($content->statusCounts());
if ($statusTotal !== $records) {
    $errors[] = "Contradictory totals: status counts sum to {$statusTotal} but there are {$records} functionality records.";
}
if ($records > 0 && $areas === 0) {
    $errors[] = "Contradictory totals: {$records} functionality records but 0 functional areas.";
}

// ---- generated snapshot search index resolves -----------------------------
// Only meaningful for the active model — /docs is the snapshot of the active
// .vibekb/, not of a bundled example.
$searchJson = $repoRoot . '/docs/assets/data/search.json';
if ($contentRoot === $repoRoot . '/.vibekb' && is_file($searchJson)) {
    $idx = json_decode((string) file_get_contents($searchJson), true);
    if (is_array($idx)) {
        $searchDir = $repoRoot . '/docs/search';
        foreach ($idx as $entry) {
            $url = (string) ($entry['url'] ?? '');
            $path = explode('#', explode('?', $url)[0])[0];
            if ($path === '') {
                continue;
            }
            if (realpath($searchDir . '/' . $path) === false) {
                $errors[] = "Static search entry points to a missing page: {$url}";
            }
        }
    } else {
        $errors[] = 'docs/assets/data/search.json is not valid JSON.';
    }
}

// ---- explainable-diagram topology summary ---------------------------------
$topoCount = 0;
$nodeCount = 0;
$edgeCount = 0;
foreach ($content->allDiagrams() as $did => $rec) {
    $topo = $content->diagramTopology((string) $did);
    if ($topo !== null) {
        $topoCount++;
        $nodeCount += count($topo['nodes']);
        $edgeCount += count($topo['edges']);
    }
}

// ---- report ---------------------------------------------------------------
echo "VibeKB content validation\n";
echo "  functionality records: {$records}  functional areas: {$areas}  diagrams: " . count($content->allDiagrams()) . "\n";
echo "  explainable topologies: {$topoCount}  nodes: {$nodeCount}  edges: {$edgeCount}\n";
echo '  errors: ' . count($errors) . '  warnings: ' . count($warnings) . "\n";

foreach ($errors as $e) {
    echo "  ERROR  {$e}\n";
}
foreach ($warnings as $w) {
    echo "  warn   {$w}\n";
}

if ($errors !== []) {
    echo "FAILED\n";
    exit(1);
}
echo "OK\n";
exit(0);
