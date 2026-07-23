<?php

declare(strict_types=1);

/**
 * VibeKB self-maintenance CLI.
 *
 * One discoverable entry point for the maintenance lifecycle a coding agent runs
 * while developing VibeKB (or any repository VibeKB is initialized in):
 *
 *   php tools/vibekb.php status              Session start: provenance, current
 *                                            work, handoff next-action, and a
 *                                            one-line validation + drift summary.
 *   php tools/vibekb.php check [--strict]     Consistency gate: validation,
 *                                            broken file references, drift since
 *                                            the recorded commit, and /docs sync.
 *   php tools/vibekb.php affected <file>...    Map files to the functionality they
 *   php tools/vibekb.php affected --since REF  likely affect (via files[] links).
 *   php tools/vibekb.php bootstrap [--dry-run] Verify/repair the .vibekb/ workspace
 *                                            (create missing dirs + starter files).
 *   php tools/vibekb.php validate [path]       Run the headless model validator.
 *   php tools/vibekb.php generate              Regenerate the /docs snapshot.
 *   php tools/vibekb.php help
 *
 * Honesty boundary: this tool *detects* things mechanically (git diff, path
 * existence, a render-and-diff). It never *interprets* what a change means for
 * the model — that is an agent's job, and the output says so. It exits non-zero
 * only on definite errors (validation errors or broken file references); drift
 * and snapshot staleness are reported, not fatal, unless --strict is given.
 *
 * PHP 8.2+ CLI only. No Composer, no Node, no network.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

$repoRoot = dirname(__DIR__);
require_once $repoRoot . '/guide/lib/helpers.php';
require_once $repoRoot . '/guide/lib/Content.php';
require_once $repoRoot . '/guide/lib/Provenance.php';
require_once __DIR__ . '/lib/Starter.php';

/** Source areas VibeKB's model describes. A change under one of these is "code";
 * changes to the model (.vibekb/), generated output (docs/), the installable
 * template payload (template/), examples/, and VCS plumbing are not code drift. */
const VIBEKB_DRIFT_EXCLUDE_PREFIXES = ['.vibekb/', 'docs/', 'template/', 'examples/', '.git/', '.github/', '.cursor/', 'VibeKBbackup/'];

// ---- small utilities ------------------------------------------------------

/** Run a git command in the repo, returning trimmed stdout ('' on failure). */
function vibekb_git(string $repoRoot, string $args): string
{
    $cmd = 'git -C ' . escapeshellarg($repoRoot) . ' ' . $args . ' 2>/dev/null';
    $out = @shell_exec($cmd);
    return is_string($out) ? trim($out) : '';
}

/** The bare commit hash recorded in the manifest provenance, or ''. */
function vibekb_recorded_commit(Content $content): string
{
    $p = provenance_data($content->manifest());
    $commit = (string) ($p['source_commit'] ?? '');
    return preg_match('/[0-9a-f]{7,40}/i', $commit, $m) ? $m[0] : '';
}

/**
 * Repository-relative paths from `git status --porcelain`, robust to the
 * status column. (vibekb_git() trims the whole output, which strips the leading
 * space of a worktree-only status on the first line; a fixed substr would then
 * drop a character. Matching the status codes explicitly avoids that.)
 *
 * @return list<string>
 */
function vibekb_porcelain_paths(string $repoRoot): array
{
    $paths = [];
    foreach (explode("\n", vibekb_git($repoRoot, 'status --porcelain')) as $line) {
        if (trim($line) === '') {
            continue;
        }
        $p = trim((string) preg_replace('/^[ MADRCU?!]{1,2}\s+/', '', $line));
        if ($p !== '' && str_contains($p, ' -> ')) { // rename: keep the new path
            $p = trim(explode(' -> ', $p)[1]);
        }
        if ($p !== '') {
            $paths[] = $p;
        }
    }
    return $paths;
}

/** Is a repository-relative path inside a modelled source area (not model/docs/examples)? */
function vibekb_is_source_path(string $path): bool
{
    $path = ltrim(str_replace('\\', '/', $path), '/');
    if ($path === '') {
        return false;
    }
    foreach (VIBEKB_DRIFT_EXCLUDE_PREFIXES as $prefix) {
        if (str_starts_with($path, $prefix)) {
            return false;
        }
    }
    return true;
}

/**
 * Build a reverse index from repository-relative file path to the model records
 * that reference it: functionality ids, whether it is a curated important file,
 * and the diagram topologies that display it.
 *
 * @return array{functionality: array<string,list<string>>, important: array<string,bool>, diagrams: array<string,list<string>>, all_referenced: array<string,bool>}
 */
function vibekb_file_index(Content $content): array
{
    $functionality = [];
    $important = [];
    $diagrams = [];
    $all = [];

    $touch = static function (string $path) use (&$all): string {
        $path = ltrim(str_replace('\\', '/', trim($path)), '/');
        if ($path !== '') {
            $all[$path] = true;
        }
        return $path;
    };

    foreach ($content->allFunctionality() as $id => $rec) {
        foreach ($content->asList($rec['meta']['files'] ?? []) as $f) {
            $p = $touch($f);
            if ($p !== '') {
                $functionality[$p][] = (string) $id;
            }
        }
    }
    foreach ($content->files() as $file) {
        $p = $touch((string) ($file['path'] ?? ''));
        if ($p !== '') {
            $important[$p] = true;
        }
    }
    foreach ($content->allDiagrams() as $did => $rec) {
        $topo = $content->diagramTopology((string) $did);
        if ($topo === null) {
            continue;
        }
        foreach ($topo['nodes'] as $n) {
            foreach ((array) ($n['files'] ?? []) as $f) {
                if (is_array($f)) {
                    $p = $touch((string) ($f['path'] ?? ''));
                    if ($p !== '') {
                        $diagrams[$p][] = (string) $did;
                    }
                }
            }
        }
        foreach ($topo['edges'] as $e) {
            foreach ((array) ($e['files'] ?? []) as $f) {
                if (is_array($f)) {
                    $p = $touch((string) ($f['path'] ?? ''));
                    if ($p !== '') {
                        $diagrams[$p][] = (string) $did;
                    }
                }
            }
        }
    }

    foreach ($diagrams as $p => $ids) {
        $diagrams[$p] = array_values(array_unique($ids));
    }
    foreach ($functionality as $p => $ids) {
        $functionality[$p] = array_values(array_unique($ids));
    }

    return ['functionality' => $functionality, 'important' => $important, 'diagrams' => $diagrams, 'all_referenced' => $all];
}

/** Split loader issues into [errors, warnings]. @return array{0:list<string>,1:list<string>} */
function vibekb_issues(Content $content): array
{
    $errors = [];
    $warnings = [];
    foreach ($content->issues() as $i) {
        if ($i['level'] === 'error') {
            $errors[] = $i['message'];
        } else {
            $warnings[] = $i['message'];
        }
    }
    return [$errors, $warnings];
}

/**
 * Changed source files since the recorded commit plus uncommitted working-tree
 * changes, filtered to modelled source areas.
 *
 * @return array{available: bool, files: list<string>, base: string}
 */
function vibekb_changed_source(string $repoRoot, Content $content): array
{
    $base = vibekb_recorded_commit($content);
    $isRepo = vibekb_git($repoRoot, 'rev-parse --is-inside-work-tree') === 'true';
    if (!$isRepo) {
        return ['available' => false, 'files' => [], 'base' => $base];
    }
    $haveBase = $base !== '' && vibekb_git($repoRoot, 'cat-file -e ' . escapeshellarg($base . '^{commit}') . ' && echo ok') === 'ok';

    $paths = [];
    if ($haveBase) {
        foreach (explode("\n", vibekb_git($repoRoot, 'diff --name-only ' . escapeshellarg($base) . ' HEAD')) as $line) {
            if (trim($line) !== '') {
                $paths[$line] = true;
            }
        }
    }
    // Uncommitted (staged + unstaged + untracked) changes, always included.
    foreach (vibekb_porcelain_paths($repoRoot) as $p) {
        $paths[$p] = true;
    }

    $files = array_values(array_filter(array_keys($paths), 'vibekb_is_source_path'));
    sort($files);
    return ['available' => $haveBase || $files !== [], 'files' => $files, 'base' => $base];
}

/** Recursively list files under a dir as paths relative to it. @return list<string> */
function vibekb_list_rel(string $dir): array
{
    if (!is_dir($dir)) {
        return [];
    }
    $out = [];
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $file) {
        if ($file->isFile()) {
            $out[] = ltrim(str_replace('\\', '/', substr($file->getPathname(), strlen($dir))), '/');
        }
    }
    sort($out);
    return $out;
}

/**
 * Normalise generated HTML so the sync check compares rendered *content*, not
 * generation metadata. Two things are inherently per-generation and must be
 * ignored: the generation timestamp, and the generator's HEAD commit (a snapshot
 * is committed *in* a commit, so it can never contain its own commit hash — a
 * fresh render at the new HEAD would otherwise always look "newer"). Stripping
 * both keeps "in sync" meaning "renders the same model", which is what matters.
 */
function vibekb_normalise_generated(string $html): string
{
    $html = str_replace('__SYNCCHECK__', '', $html);
    // The "Generated by VibeKB commit" provenance row (generator commit + branch).
    $html = (string) preg_replace('#<div class="provenance__row"><dt>Generated by VibeKB commit</dt>.*?</div>#s', '', $html);
    // Any date/datetime (covers "Analysis generated" and the footer stamp time).
    return (string) preg_replace('/\d{4}-\d{2}-\d{2}(?:[ T]\d{2}:\d{2}(?::\d{2})?(?:\s*UTC)?)?/', '', $html);
}

// ---- commands -------------------------------------------------------------

/** Print the session-start orientation. Always read-only, always exit 0. */
function vibekb_cmd_status(string $repoRoot): int
{
    $content = new Content($repoRoot . '/.vibekb');
    $content->load();
    [$errors, $warnings] = vibekb_issues($content);
    $p = provenance_data($content->manifest());

    echo "VibeKB — session status\n";
    echo str_repeat('=', 60) . "\n";
    echo 'Model            : ' . (string) ($p['source_name'] ?? '(unnamed)') . "\n";
    echo 'Source commit    : ' . (($p['source_commit'] ?? '') !== '' ? $p['source_commit'] : '(none recorded)') . "\n";
    echo 'Last verified    : ' . (($p['last_verified'] ?? '') !== '' ? $p['last_verified'] : '(unknown)') . "\n";
    echo 'Updates itself   : ' . ($p['updates_automatically'] ? 'yes' : 'no — agent-maintained') . "\n";

    // Current work.
    $cw = $content->currentWork();
    echo "\nActive work\n" . str_repeat('-', 60) . "\n";
    if ($cw !== null && trim((string) ($cw['meta']['title'] ?? '')) !== '') {
        echo '  ' . (string) $cw['meta']['title'] . "\n";
        echo '  status: ' . (string) ($cw['meta']['status'] ?? '?')
            . '   verification: ' . (string) ($cw['meta']['verification_state'] ?? '?') . "\n";
        if (($cw['meta']['objective'] ?? '') !== '') {
            echo '  objective: ' . vibekb_wrap((string) $cw['meta']['objective'], 4) . "\n";
        }
    } else {
        echo "  (no current-work record)\n";
    }

    // Handoff next action.
    $ho = $content->handoff();
    echo "\nHandoff\n" . str_repeat('-', 60) . "\n";
    if ($ho !== null) {
        if (($ho['meta']['summary'] ?? '') !== '') {
            echo '  ' . vibekb_wrap((string) $ho['meta']['summary'], 2) . "\n";
        }
        $next = vibekb_extract_section((string) ($ho['body'] ?? ''), 'next recommended action');
        if ($next !== '') {
            echo "\n  Next recommended action:\n";
            echo '  ' . vibekb_wrap($next, 2) . "\n";
        }
    } else {
        echo "  (no handoff record)\n";
    }

    // One-line validation + drift.
    $changed = vibekb_changed_source($repoRoot, $content);
    echo "\nModel health\n" . str_repeat('-', 60) . "\n";
    echo '  validation : ' . count($errors) . ' error(s), ' . count($warnings) . " warning(s)\n";
    if (!$changed['available']) {
        echo "  drift      : cannot compare (no git history for the recorded commit)\n";
    } elseif ($changed['files'] === []) {
        echo "  drift      : no source changes since the recorded commit\n";
    } else {
        echo '  drift      : ' . count($changed['files']) . " source file(s) changed since the recorded commit — run `php tools/vibekb.php check`\n";
    }
    echo "\nStart here: `php tools/vibekb.php check` before committing; see CLAUDE.md for the full lifecycle.\n";
    return 0;
}

/** The consistency gate. Returns an exit code. */
function vibekb_cmd_check(string $repoRoot, bool $strict): int
{
    $content = new Content($repoRoot . '/.vibekb');
    $content->load();
    [$errors, $warnings] = vibekb_issues($content);
    $index = vibekb_file_index($content);

    $failed = false;
    echo "VibeKB consistency check\n" . str_repeat('=', 60) . "\n";

    // 1. Model validation.
    echo "\n[1] Model validation\n" . str_repeat('-', 60) . "\n";
    echo '  ' . count($errors) . ' error(s), ' . count($warnings) . " warning(s)\n";
    foreach ($errors as $e) {
        echo "  ERROR  {$e}\n";
    }
    if ($errors !== []) {
        $failed = true;
    }

    // 2. Broken file references (definite error).
    echo "\n[2] Broken file references (detected)\n" . str_repeat('-', 60) . "\n";
    $broken = [];
    foreach (array_keys($index['all_referenced']) as $path) {
        // Only check paths that look like repository files (skip anything that
        // escaped normalisation). A referenced path must exist on disk.
        if (str_contains($path, '..')) {
            continue;
        }
        if (!file_exists($repoRoot . '/' . $path)) {
            $broken[] = $path;
        }
    }
    if ($broken === []) {
        echo "  none — every file the model references exists.\n";
    } else {
        foreach ($broken as $b) {
            $who = $index['functionality'][$b] ?? [];
            $tag = $who !== [] ? ' (referenced by: ' . implode(', ', $who) . ')' : '';
            echo "  ERROR  missing file: {$b}{$tag}\n";
        }
        $failed = true;
    }

    // 3. Source changes since the recorded commit (detected → needs interpretation).
    echo "\n[3] Source changes since the recorded commit (detected \u{2192} needs interpretation)\n" . str_repeat('-', 60) . "\n";
    $changed = vibekb_changed_source($repoRoot, $content);
    if (!$changed['available']) {
        echo "  cannot compare — no git history for the recorded commit (" . ($changed['base'] ?: 'none') . ").\n";
    } elseif ($changed['files'] === []) {
        echo "  no source changes since the recorded commit (" . ($changed['base'] ?: 'working tree') . ").\n";
    } else {
        echo '  base commit: ' . ($changed['base'] ?: '(unknown)') . "\n";
        $unmapped = [];
        foreach ($changed['files'] as $f) {
            $fns = $index['functionality'][$f] ?? [];
            $imp = !empty($index['important'][$f]);
            $dgs = $index['diagrams'][$f] ?? [];
            if ($fns === [] && !$imp && $dgs === []) {
                $unmapped[] = $f;
                continue;
            }
            $bits = [];
            if ($fns !== []) {
                $bits[] = 'functionality: ' . implode(', ', $fns);
            }
            if ($imp) {
                $bits[] = 'important-file';
            }
            if ($dgs !== []) {
                $bits[] = 'diagram: ' . implode(', ', $dgs);
            }
            echo "  ~ {$f}\n      \u{2192} " . implode(' · ', $bits) . "\n";
        }
        if ($unmapped !== []) {
            echo "\n  Unmapped changed source files (may need a new or updated record):\n";
            foreach ($unmapped as $u) {
                echo "      ? {$u}\n";
            }
        }
        echo "\n  Note: these were detected mechanically. Whether the model needs updating\n";
        echo "  is an interpretation only an agent can make — review each affected record.\n";
    }

    // 4. Snapshot sync (detected).
    echo "\n[4] Snapshot sync — /docs vs a fresh render (detected)\n" . str_repeat('-', 60) . "\n";
    $sync = vibekb_check_snapshot($repoRoot);
    if ($sync['status'] === 'no-docs') {
        echo "  /docs is not present — nothing to compare (run `generate` to publish it).\n";
    } elseif ($sync['status'] === 'error') {
        echo '  could not compare: ' . $sync['message'] . "\n";
    } elseif ($sync['status'] === 'in-sync') {
        echo "  /docs matches a fresh render.\n";
    } else {
        echo "  /docs is STALE — it differs from a fresh render. Run `php tools/generate-static.php`.\n";
        foreach (array_slice($sync['diffs'], 0, 12) as $d) {
            echo "      {$d}\n";
        }
        if (count($sync['diffs']) > 12) {
            echo '      … and ' . (count($sync['diffs']) - 12) . " more.\n";
        }
        if ($strict) {
            $failed = true;
        }
    }

    echo "\n" . str_repeat('=', 60) . "\n";
    echo $failed ? "RESULT: FAILED (definite errors above)\n" : "RESULT: OK (no definite errors)\n";
    return $failed ? 1 : 0;
}

/**
 * Regenerate the snapshot into a temp dir and compare against /docs.
 *
 * @return array{status: string, diffs: list<string>, message: string}
 */
function vibekb_check_snapshot(string $repoRoot): array
{
    $docs = $repoRoot . '/docs';
    if (!is_dir($docs) || !is_file($docs . '/index.html')) {
        return ['status' => 'no-docs', 'diffs' => [], 'message' => ''];
    }
    $tmp = sys_get_temp_dir() . '/vibekb-synccheck-' . getmypid() . '-' . mt_rand(1000, 9999);
    putenv('VIBEKB_DOCS_OUT=' . $tmp);
    putenv('VIBEKB_GENERATED=__SYNCCHECK__');
    $out = @shell_exec('php ' . escapeshellarg($repoRoot . '/tools/generate-static.php') . ' 2>&1');
    putenv('VIBEKB_DOCS_OUT');
    putenv('VIBEKB_GENERATED');

    if (!is_dir($tmp) || !is_file($tmp . '/index.html')) {
        vibekb_rrmdir($tmp);
        return ['status' => 'error', 'diffs' => [], 'message' => 'the generator did not produce output (' . trim((string) $out) . ')'];
    }

    $diffs = [];
    $fresh = vibekb_list_rel($tmp);
    $current = vibekb_list_rel($docs);
    // The drift check compares the generated site only; ignore files /docs may
    // legitimately carry that the generator does not manage.
    $freshSet = array_flip($fresh);
    foreach ($fresh as $rel) {
        if (!in_array($rel, $current, true)) {
            $diffs[] = "only in fresh render: {$rel}";
            continue;
        }
        $a = vibekb_normalise_generated((string) @file_get_contents($tmp . '/' . $rel));
        $b = vibekb_normalise_generated((string) @file_get_contents($docs . '/' . $rel));
        if ($a !== $b) {
            $diffs[] = "differs: {$rel}";
        }
    }
    foreach ($current as $rel) {
        // A page the generator would produce but that is missing from a fresh
        // render means /docs has stale generated pages. Only flag generated-looking
        // paths (html/assets), never hand-added extras.
        if (!isset($freshSet[$rel]) && (str_ends_with($rel, '.html') || str_starts_with($rel, 'assets/'))) {
            $diffs[] = "stale in /docs (not in fresh render): {$rel}";
        }
    }

    vibekb_rrmdir($tmp);
    return ['status' => $diffs === [] ? 'in-sync' : 'stale', 'diffs' => $diffs, 'message' => ''];
}

/** affected <file>... | --since REF */
function vibekb_cmd_affected(string $repoRoot, array $args): int
{
    $content = new Content($repoRoot . '/.vibekb');
    $content->load();
    $index = vibekb_file_index($content);

    $files = [];
    if (($args[0] ?? '') === '--since') {
        $ref = $args[1] ?? '';
        if ($ref === '') {
            fwrite(STDERR, "affected --since needs a git ref.\n");
            return 2;
        }
        foreach (explode("\n", vibekb_git($repoRoot, 'diff --name-only ' . escapeshellarg($ref) . ' HEAD')) as $line) {
            if (trim($line) !== '' && vibekb_is_source_path($line)) {
                $files[] = trim($line);
            }
        }
        foreach (vibekb_porcelain_paths($repoRoot) as $p) {
            if (vibekb_is_source_path($p)) {
                $files[] = $p;
            }
        }
        $files = array_values(array_unique($files));
    } else {
        $files = $args;
    }

    if ($files === []) {
        echo "No files given (and no changes found). Usage: affected <file>... | --since REF\n";
        return 0;
    }

    echo "Likely affected functionality (from files[] back-links)\n" . str_repeat('=', 60) . "\n";
    $anyUnmapped = false;
    foreach ($files as $f) {
        $f = ltrim(str_replace('\\', '/', trim($f)), '/');
        $fns = $index['functionality'][$f] ?? [];
        $imp = !empty($index['important'][$f]);
        $dgs = $index['diagrams'][$f] ?? [];
        echo "\n{$f}\n";
        if ($fns === [] && !$imp && $dgs === []) {
            echo "  (no record references this file — may need a new or updated record)\n";
            $anyUnmapped = true;
            continue;
        }
        if ($fns !== []) {
            foreach ($fns as $id) {
                $rec = $content->functionality($id);
                $title = $rec !== null ? (string) ($rec['meta']['title'] ?? $id) : $id;
                echo "  functionality: {$id}  ({$title})\n";
            }
        }
        if ($imp) {
            echo "  important-file: yes (see files/important-files.json)\n";
        }
        if ($dgs !== []) {
            echo '  diagram topology: ' . implode(', ', $dgs) . "\n";
        }
    }
    echo "\nThis is a heuristic from recorded back-links; use judgement — a listed record\nmay be unaffected, and an unmapped file may still need a record.\n";
    return $anyUnmapped ? 0 : 0;
}

/**
 * bootstrap [--dry-run] — "git init for VibeKB".
 *
 * Verifies the `.vibekb/` workspace and repairs anything missing: creates the
 * required directories and writes any missing starter files, without ever
 * overwriting existing content. It is the deterministic counterpart to the
 * installer's model step, safe to run against a fresh, partial, or damaged
 * workspace.
 *
 * Honesty boundary: bootstrap NEVER generates functionality, invents diagrams,
 * inspects the target's source, or writes documentation about the software. It
 * only lays down valid, empty scaffolding — an agent builds the model.
 */
function vibekb_cmd_bootstrap(string $repoRoot, bool $dryRun): int
{
    $vibekbRoot = $repoRoot . '/.vibekb';
    $ctx = ['project_name' => basename(rtrim($repoRoot, '/'))];

    echo "VibeKB bootstrap" . ($dryRun ? ' (dry run)' : '') . "\n" . str_repeat('=', 60) . "\n";
    echo 'Workspace: ' . $vibekbRoot . "\n";

    $before = vibekb_verify_workspace($vibekbRoot, $ctx);
    if (!$before['present']) {
        echo "\n  No .vibekb/ workspace found — creating a fresh one.\n";
    }

    $report = vibekb_scaffold_workspace($vibekbRoot, $ctx, $dryRun, false);

    echo "\nStructure\n" . str_repeat('-', 60) . "\n";
    $verb = $dryRun ? 'would create' : 'created';
    if ($report['created_dirs'] === [] && $report['created_files'] === []) {
        echo "  Everything is in place — nothing to repair.\n";
    } else {
        if ($report['created_dirs'] !== []) {
            echo '  ' . ucfirst($verb) . ' ' . count($report['created_dirs']) . " director(ies):\n";
            foreach ($report['created_dirs'] as $d) {
                echo "      + {$d}\n";
            }
        }
        if ($report['created_files'] !== []) {
            echo '  ' . ucfirst($verb) . ' ' . count($report['created_files']) . " starter file(s):\n";
            foreach ($report['created_files'] as $f) {
                echo "      + {$f}\n";
            }
        }
    }
    if ($report['kept_files'] !== []) {
        echo '  Kept ' . count($report['kept_files']) . " existing file(s) untouched.\n";
    }
    if ($report['errors'] !== []) {
        echo "\n  Errors:\n";
        foreach ($report['errors'] as $e) {
            echo "  ERROR  {$e}\n";
        }
        return 1;
    }

    echo "\n" . str_repeat('=', 60) . "\n";
    if ($dryRun) {
        echo "Dry run complete. No changes were made.\n";
        return 0;
    }

    $after = vibekb_verify_workspace($vibekbRoot, $ctx);
    if ($after['ok']) {
        echo "RESULT: OK — the workspace is complete and valid.\n";
        echo "Next: build the model with prompts/INTEGRATE_VIBEKB.md, or run `php tools/vibekb.php status`.\n";
        return 0;
    }
    echo "RESULT: incomplete — still missing "
        . count($after['missing_dirs']) . " dir(s) and " . count($after['missing_files']) . " file(s).\n";
    return 1;
}

// ---- text helpers ---------------------------------------------------------

function vibekb_wrap(string $text, int $indent): string
{
    $text = trim(preg_replace('/\s+/', ' ', $text) ?? '');
    $pad = str_repeat(' ', $indent);
    return wordwrap($text, 76 - $indent, "\n" . $pad);
}

/** Pull the first paragraph under a `## …<needle>…` heading from a Markdown body. */
function vibekb_extract_section(string $body, string $needle): string
{
    $lines = explode("\n", $body);
    $needle = strtolower($needle);
    $capturing = false;
    $buf = [];
    foreach ($lines as $line) {
        if (preg_match('/^#{1,6}\s+(.*)$/', $line, $m)) {
            if ($capturing) {
                break;
            }
            $capturing = str_contains(strtolower($m[1]), $needle);
            continue;
        }
        if ($capturing) {
            if (trim($line) === '' && $buf !== []) {
                break;
            }
            if (trim($line) !== '') {
                $buf[] = trim($line);
            }
        }
    }
    return trim(implode(' ', $buf));
}

function vibekb_rrmdir(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }
    foreach (scandir($dir) ?: [] as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }
        $path = $dir . '/' . $entry;
        is_dir($path) ? vibekb_rrmdir($path) : @unlink($path);
    }
    @rmdir($dir);
}

function vibekb_usage(): void
{
    echo <<<TXT
VibeKB self-maintenance CLI

  php tools/vibekb.php status              Session start: provenance, current work,
                                           handoff next-action, validation + drift.
  php tools/vibekb.php check [--strict]    Validation + broken references + drift
                                           since the recorded commit + /docs sync.
  php tools/vibekb.php affected <file>...   Map files to likely functionality.
  php tools/vibekb.php affected --since REF  ...for everything changed since REF.
  php tools/vibekb.php bootstrap [--dry-run] Verify and repair the .vibekb/
                                           workspace (git-init for VibeKB).
  php tools/vibekb.php validate [path]      Run the headless model validator.
  php tools/vibekb.php generate             Regenerate the /docs snapshot.
  php tools/vibekb.php help

Detection vs interpretation: this tool detects changes mechanically; deciding what
a change means for the model is an agent's job. It exits non-zero only on definite
errors (validation errors or broken file references); --strict also fails on a
stale /docs. See CLAUDE.md for the full lifecycle.

TXT;
}

// ---- dispatch -------------------------------------------------------------

$argvLocal = $argv;
array_shift($argvLocal); // script name
$command = $argvLocal[0] ?? 'status';
$rest = array_slice($argvLocal, 1);

switch ($command) {
    case 'status':
        exit(vibekb_cmd_status($repoRoot));
    case 'check':
        exit(vibekb_cmd_check($repoRoot, in_array('--strict', $rest, true)));
    case 'affected':
        exit(vibekb_cmd_affected($repoRoot, $rest));
    case 'bootstrap':
        exit(vibekb_cmd_bootstrap($repoRoot, in_array('--dry-run', $rest, true)));
    case 'validate':
        $arg = ($rest[0] ?? '') !== '' ? ' ' . escapeshellarg($rest[0]) : '';
        passthru('php ' . escapeshellarg($repoRoot . '/tools/validate.php') . $arg, $code);
        exit($code);
    case 'generate':
        passthru('php ' . escapeshellarg($repoRoot . '/tools/generate-static.php'), $code);
        exit($code);
    case 'help':
    case '--help':
    case '-h':
        vibekb_usage();
        exit(0);
    default:
        fwrite(STDERR, "Unknown command: {$command}\n\n");
        vibekb_usage();
        exit(2);
}
