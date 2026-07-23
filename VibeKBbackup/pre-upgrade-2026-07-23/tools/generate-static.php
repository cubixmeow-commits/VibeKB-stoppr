<?php

declare(strict_types=1);

/**
 * VibeKB static snapshot generator (Mode B).
 *
 * Renders the *same* guide templates the dynamic PHP app uses (Mode A) into a
 * self-contained static site under `/docs`, suitable for GitHub Pages or any
 * static host. There is no second template system: this script only swaps the
 * URL strategy to emit relative, subpath-safe links and writes the rendered
 * HTML to disk.
 *
 * Usage:
 *   php tools/generate-static.php            # regenerate /docs
 *   VIBEKB_GENERATED="2026-07-21" php tools/generate-static.php   # fixed stamp
 *
 * Requirements: PHP 8.2+ CLI only. No Composer, no Node, no network. The output
 * is a snapshot of the source commit at generation time and does NOT update
 * itself — re-run this script to refresh it.
 *
 * The script overwrites only the generated site (index.html, the view
 * directories, and assets/). It leaves other files in /docs (such as
 * STOPPR_INTEGRATION_AUDIT.md) untouched.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

$repoRoot = dirname(__DIR__);
$guideLib = $repoRoot . '/guide/lib';

require_once $guideLib . '/helpers.php';
require_once $guideLib . '/Content.php';
require_once $guideLib . '/Provenance.php';
require_once $guideLib . '/nav.php';
require_once $guideLib . '/search.php';

$contentRoot = $repoRoot . '/.vibekb';
$docsRoot = $repoRoot . '/docs';

$content = new Content($contentRoot);
$content->load();

// Fail loudly on content errors — a snapshot must not ship a broken model.
$errors = array_values(array_filter($content->issues(), fn ($i) => $i['level'] === 'error'));
if ($errors !== []) {
    fwrite(STDERR, "Refusing to generate: content has validation errors:\n");
    foreach ($errors as $e) {
        fwrite(STDERR, '  - ' . $e['message'] . "\n");
    }
    exit(1);
}

// ---- generation provenance (the snapshot event) --------------------------
$git = static function (string $args) use ($repoRoot): string {
    $cmd = 'git -C ' . escapeshellarg($repoRoot) . ' ' . $args . ' 2>/dev/null';
    $out = @shell_exec($cmd);
    return is_string($out) ? trim($out) : '';
};
$generatedAt = getenv('VIBEKB_GENERATED') ?: gmdate('Y-m-d H:i') . ' UTC';
$generation = [
    'mode' => 'static',
    'generated' => $generatedAt,
    'generator_repository' => 'cubixmeow-commits/VibeKB',
    'generator_commit' => $git('rev-parse --short HEAD'),
    'generator_branch' => $git('rev-parse --abbrev-ref HEAD'),
];
$GLOBALS['vibekb_generation'] = $generation;

$identity = $content->projectDoc('identity');
$projectName = (string) ($identity['meta']['title'] ?? 'Software Guide');
$navPrimary = guide_nav_primary();
$navSecondary = guide_nav_secondary();
$navItems = array_merge($navPrimary, $navSecondary);
$pageTitles = guide_page_titles();

// ---- filesystem helpers ---------------------------------------------------
$ensureDir = static function (string $dir): void {
    if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
        throw new RuntimeException("Cannot create directory: {$dir}");
    }
};
$rrmdir = static function (string $dir) use (&$rrmdir): void {
    if (!is_dir($dir)) {
        return;
    }
    foreach (scandir($dir) ?: [] as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }
        $path = $dir . '/' . $entry;
        is_dir($path) ? $rrmdir($path) : @unlink($path);
    }
    @rmdir($dir);
};

// Clean only the generated site (leave docs/*.md and anything else alone).
$ensureDir($docsRoot);
foreach (['functionality', 'how-it-works', 'diagrams', 'data', 'files', 'current-work', 'changes', 'why', 'handoff', 'reference', 'search', 'assets'] as $sub) {
    $rrmdir($docsRoot . '/' . $sub);
}
@unlink($docsRoot . '/index.html');

// ---- render one page ------------------------------------------------------
$write = static function (string $relPath, string $html) use ($docsRoot, $ensureDir): void {
    $full = $docsRoot . '/' . $relPath;
    $ensureDir(dirname($full));
    file_put_contents($full, $html);
};

$renderPage = static function (
    string $relPath,
    string $view,
    array $get,
    string $bodyTemplate,
    string $pageTitle,
    array $extra
) use ($content, $projectName, $navItems, $navPrimary, $navSecondary, $write): void {
    $currentDir = trim(str_replace('\\', '/', dirname($relPath)), '.');
    $currentDir = $currentDir === '/' ? '' : trim($currentDir, '/');
    guide_url_strategy(new StaticUrlStrategy($currentDir));

    $_GET = $get;

    $vars = [
        'content' => $content,
        'projectName' => $projectName,
        'devMode' => false,
        'view' => $view,
        'pageTitle' => $pageTitle,
        'navItems' => $navItems,
        'navPrimary' => $navPrimary,
        'navSecondary' => $navSecondary,
        'bodyTemplate' => $bodyTemplate,
    ] + $extra;

    ob_start();
    render_view('layout', $vars);
    $write($relPath, (string) ob_get_clean());
};

// ---- page inventory -------------------------------------------------------
$count = 0;

$renderPage('index.html', 'overview', [], 'overview', $pageTitles['overview'], []);
$count++;

$renderPage('functionality/index.html', 'functionality', [], 'functionality-index', $pageTitles['functionality'], []);
$count++;
foreach ($content->allFunctionality() as $id => $rec) {
    $renderPage(
        'functionality/' . $id . '.html',
        'functionality',
        ['id' => (string) $id],
        'functionality-detail',
        (string) ($rec['meta']['title'] ?? $id),
        ['record' => $rec, 'id' => (string) $id]
    );
    $count++;
}

foreach (['how-it-works', 'diagrams', 'data', 'files', 'current-work', 'changes', 'handoff', 'reference', 'search'] as $view) {
    $renderPage($view . '/index.html', $view, [], $view, $pageTitles[$view] ?? ucfirst($view), []);
    $count++;
}

$renderPage('why/index.html', 'why', [], 'why', $pageTitles['why'], []);
$count++;
foreach ($content->memory() as $type => $records) {
    foreach ($records as $id => $rec) {
        $renderPage(
            'why/' . $type . '/' . $id . '.html',
            'why',
            ['type' => (string) $type, 'id' => (string) $id],
            'why',
            (string) ($rec['meta']['title'] ?? $id),
            []
        );
        $count++;
    }
}

// ---- assets ---------------------------------------------------------------
$ensureDir($docsRoot . '/assets/css');
$ensureDir($docsRoot . '/assets/js');
$ensureDir($docsRoot . '/assets/data');
$ensureDir($docsRoot . '/assets/diagrams');

copy($repoRoot . '/guide/assets/css/guide.css', $docsRoot . '/assets/css/guide.css');
copy($repoRoot . '/guide/assets/js/guide.js', $docsRoot . '/assets/js/guide.js');

foreach (glob($contentRoot . '/diagrams/assets/*.svg') ?: [] as $svg) {
    copy($svg, $docsRoot . '/assets/diagrams/' . basename($svg));
}

// Search index — built with the search page's location so links resolve.
$searchIndex = build_search_index($content, new StaticUrlStrategy('search'));
file_put_contents(
    $docsRoot . '/assets/data/search.json',
    json_encode($searchIndex, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
);

// GitHub Pages: skip Jekyll so nothing is silently rewritten or ignored.
file_put_contents($docsRoot . '/.nojekyll', "");

fwrite(STDOUT, "Generated {$count} pages + assets into /docs\n");
fwrite(STDOUT, "  mode=static generated={$generatedAt} commit={$generation['generator_commit']} branch={$generation['generator_branch']}\n");
fwrite(STDOUT, "  entry point: docs/index.html\n");
