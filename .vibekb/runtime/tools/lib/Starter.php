<?php

declare(strict_types=1);

/**
 * VibeKB starter-workspace loader — reads the single, language-neutral starter
 * definition and turns it into a fresh, empty `.vibekb/` model.
 *
 * The definition itself is **data**, not code: it lives under
 * `template/starter/` (a `starter.json` directory list plus a `files/` tree that
 * mirrors onto the target's `.vibekb/`). That one canonical definition is read by
 * two consumers so they can never disagree about what a fresh model contains:
 *
 *   - this PHP loader (used by `php tools/vibekb.php bootstrap`), and
 *   - the native Go installer, which embeds the same `template/starter/` tree.
 *
 * Files carry two tokens, substituted per install: `{{DATE}}` and
 * `{{PROJECT_NAME_JSON}}` (a JSON-encoded project name, quotes included).
 *
 * Honesty boundary: the scaffolding describes VibeKB's *content model*, never the
 * target's *software*. Every starter record is explicitly a placeholder telling
 * an agent what to write; none claims the target does anything. This loader never
 * inspects source, invents functionality, or writes documentation about the app.
 *
 * PHP 8.2+, no Composer, no framework, no network. Windows/macOS/Linux safe.
 */

/** Absolute path to the canonical starter definition directory. */
function vibekb_starter_root(): string
{
    // tools/lib/Starter.php → repo/target root is two directories up.
    return dirname(__DIR__, 2) . '/template/starter';
}

/**
 * The directories a fresh `.vibekb/` workspace must contain, relative to it.
 * Some hold starter files; some (records/, assets/, sessions/, memory types) are
 * intentionally empty until an agent adds content, so bootstrap can still confirm
 * and recreate them. Read from the canonical `starter.json`.
 *
 * @return list<string>
 */
function vibekb_starter_dirs(): array
{
    $json = @file_get_contents(vibekb_starter_root() . '/starter.json');
    $data = is_string($json) ? json_decode($json, true) : null;
    $dirs = (is_array($data) && isset($data['dirs']) && is_array($data['dirs'])) ? $data['dirs'] : [];
    $out = [];
    foreach ($dirs as $d) {
        $d = trim((string) $d);
        if ($d !== '') {
            $out[] = $d;
        }
    }
    return $out;
}

/**
 * The starter files that must exist inside a `.vibekb/` workspace, as a map of
 * path (relative to the `.vibekb/` root) to file contents, with tokens
 * substituted. These are the scaffolding an empty-but-valid model needs so it
 * loads cleanly and passes `php tools/vibekb.php check`, while making it obvious
 * the model has not been built yet.
 *
 * Contents come verbatim from `template/starter/files/`; only `{{DATE}}` and
 * `{{PROJECT_NAME_JSON}}` are replaced — never to describe the software.
 *
 * @param array<string,string> $ctx Optional context: `date` (YYYY-MM-DD),
 *                                   `project_name`.
 * @return array<string,string>
 */
function vibekb_starter_files(array $ctx = []): array
{
    $date = $ctx['date'] ?? date('Y-m-d');
    $name = trim($ctx['project_name'] ?? '') !== '' ? $ctx['project_name'] : 'This project';
    $replacements = [
        '{{DATE}}' => $date,
        '{{PROJECT_NAME_JSON}}' => (string) json_encode($name, JSON_UNESCAPED_SLASHES),
    ];

    $filesRoot = vibekb_starter_root() . '/files';
    if (!is_dir($filesRoot)) {
        return [];
    }

    $out = [];
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($filesRoot, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($it as $file) {
        if (!$file->isFile()) {
            continue;
        }
        $rel = ltrim(str_replace('\\', '/', substr($file->getPathname(), strlen($filesRoot))), '/');
        $contents = (string) file_get_contents($file->getPathname());
        $out[$rel] = strtr($contents, $replacements);
    }
    ksort($out);
    return $out;
}

/**
 * Inspect a `.vibekb/` workspace and report which starter directories and files
 * are missing, without changing anything. Used by `bootstrap` (and the native
 * installer's parity) to describe the state of an installation.
 *
 * @return array{ok: bool, missing_dirs: list<string>, missing_files: list<string>, present: bool}
 */
function vibekb_verify_workspace(string $vibekbRoot, array $ctx = []): array
{
    $vibekbRoot = rtrim($vibekbRoot, '/\\');
    $present = is_dir($vibekbRoot);

    $missingDirs = [];
    foreach (vibekb_starter_dirs() as $rel) {
        if (!is_dir($vibekbRoot . '/' . $rel)) {
            $missingDirs[] = $rel;
        }
    }

    $missingFiles = [];
    foreach (array_keys(vibekb_starter_files($ctx)) as $rel) {
        if (!is_file($vibekbRoot . '/' . $rel)) {
            $missingFiles[] = $rel;
        }
    }

    return [
        'ok' => $present && $missingDirs === [] && $missingFiles === [],
        'missing_dirs' => $missingDirs,
        'missing_files' => $missingFiles,
        'present' => $present,
    ];
}

/**
 * Create or repair a `.vibekb/` workspace: create every required directory and
 * write every missing starter file. Existing files are left untouched (never
 * overwritten) unless `$force` is true, so it is always safe to run against a
 * partial or damaged workspace — the "git init for VibeKB" guarantee.
 *
 * When `$dryRun` is true nothing is written; the returned report describes what
 * would happen.
 *
 * @param array<string,string> $ctx Context passed to vibekb_starter_files().
 * @return array{created_dirs: list<string>, created_files: list<string>, kept_files: list<string>, overwritten_files: list<string>, errors: list<string>}
 */
function vibekb_scaffold_workspace(string $vibekbRoot, array $ctx = [], bool $dryRun = false, bool $force = false): array
{
    $vibekbRoot = rtrim($vibekbRoot, '/\\');
    $report = [
        'created_dirs' => [],
        'created_files' => [],
        'kept_files' => [],
        'overwritten_files' => [],
        'errors' => [],
    ];

    // The workspace root itself.
    if (!is_dir($vibekbRoot)) {
        $report['created_dirs'][] = '.';
        if (!$dryRun && !@mkdir($vibekbRoot, 0755, true) && !is_dir($vibekbRoot)) {
            $report['errors'][] = "Could not create workspace directory: {$vibekbRoot}";
            return $report;
        }
    }

    foreach (vibekb_starter_dirs() as $rel) {
        $path = $vibekbRoot . '/' . $rel;
        if (!is_dir($path)) {
            $report['created_dirs'][] = $rel;
            if (!$dryRun && !@mkdir($path, 0755, true) && !is_dir($path)) {
                $report['errors'][] = "Could not create directory: {$rel}";
            }
        }
    }

    foreach (vibekb_starter_files($ctx) as $rel => $contents) {
        $path = $vibekbRoot . '/' . $rel;
        $exists = is_file($path);
        if ($exists && !$force) {
            $report['kept_files'][] = $rel;
            continue;
        }
        if ($exists && $force) {
            $report['overwritten_files'][] = $rel;
        } else {
            $report['created_files'][] = $rel;
        }
        if ($dryRun) {
            continue;
        }
        $dir = dirname($path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        if (@file_put_contents($path, $contents) === false) {
            $report['errors'][] = "Could not write file: {$rel}";
        }
    }

    return $report;
}
