<?php

declare(strict_types=1);

/**
 * VibeKB V1 — the Software Guide front controller.
 *
 * A single entry point that routes by `?view=`. Query-string routing keeps the
 * app deployable on ordinary cPanel shared hosting (no rewrite rules) and in a
 * subfolder. Every view is rendered from repository-owned content in
 * `../.vibekb/`.
 */

require_once __DIR__ . '/lib/helpers.php';
require_once __DIR__ . '/lib/Content.php';
require_once __DIR__ . '/lib/Provenance.php';
require_once __DIR__ . '/lib/nav.php';
require_once __DIR__ . '/lib/search.php';
require_once __DIR__ . '/lib/map.php';

// Mode A — the live PHP guide. The static generator (Mode B) overrides both the
// URL strategy and this generation context.
guide_url_strategy(new DynamicUrlStrategy());
$GLOBALS['vibekb_generation'] = ['mode' => 'dynamic'];

// Revalidate the HTML each load so freshly versioned asset URLs are picked up.
if (!headers_sent()) {
    header('Cache-Control: no-cache, must-revalidate');
}

// The active model is the repository's own `.vibekb/` by default. An explicit
// VIBEKB_CONTENT_ROOT lets the same renderer preview a bundled example model
// (e.g. examples/sousmeow/.vibekb) without a second app. The path is confined
// to a `.vibekb` directory so the override can never point the guide at
// arbitrary filesystem locations.
$contentRoot = dirname(__DIR__) . '/.vibekb';
$override = getenv('VIBEKB_CONTENT_ROOT');
if (is_string($override) && $override !== '') {
    $candidate = rtrim($override, '/');
    if (basename($candidate) === '.vibekb' && is_dir($candidate)) {
        $contentRoot = $candidate;
    }
}

// Development vs production error posture.
$devMode = (getenv('VIBEKB_DEV') === '1')
    || in_array(($_SERVER['SERVER_NAME'] ?? ''), ['localhost', '127.0.0.1'], true);

$content = new Content($contentRoot);

try {
    $content->load();
} catch (Throwable $e) {
    http_response_code(500);
    if ($devMode) {
        echo '<pre>Content failed to load: ' . h($e->getMessage()) . "\n" . h($e->getTraceAsString()) . '</pre>';
    } else {
        echo 'The guide is temporarily unavailable.';
    }
    return;
}

$view = (string) ($_GET['view'] ?? 'overview');
if ($view === '') {
    $view = 'overview';
}

// Live search index endpoint (dynamic mode). Returns only public guide content.
if ($view === 'search' && ($_GET['data'] ?? '') === 'json') {
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode(
        build_search_index($content, guide_url_strategy()),
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    );
    return;
}

// Whitelist of views -> template files, plus the shared navigation and page
// titles. `functionality` is special-cased because it renders either the index
// or a detail page depending on `?id`. Defined once in lib/nav.php so the
// static generator presents the identical page inventory.
$routes = guide_routes();
$navPrimary = guide_nav_primary();
$navSecondary = guide_nav_secondary();
$navItems = array_merge($navPrimary, $navSecondary);
$pageTitles = guide_page_titles();

$identity = $content->projectDoc('identity');
$projectName = (string) ($identity['meta']['title'] ?? 'Software Guide');

$template = null;
$pageTitle = 'Overview';
$vars = ['content' => $content, 'projectName' => $projectName, 'devMode' => $devMode];

if ($view === 'functionality') {
    $id = isset($_GET['id']) ? (string) preg_replace('/[^a-z0-9\-]/i', '', (string) $_GET['id']) : '';
    if ($id !== '') {
        $record = $content->functionality($id);
        if ($record === null) {
            http_response_code(404);
            $template = 'not-found';
            $pageTitle = 'Not found';
            $vars['missing'] = 'functionality: ' . $id;
        } else {
            $template = 'functionality-detail';
            $pageTitle = (string) ($record['meta']['title'] ?? 'Functionality');
            $vars['record'] = $record;
            $vars['id'] = $id;
        }
    } else {
        $template = 'functionality-index';
        $pageTitle = 'Functionality';
    }
} elseif (array_key_exists($view, $routes) && $routes[$view] !== null) {
    $template = $routes[$view];
    $pageTitle = $pageTitles[$view] ?? ucfirst(str_replace('-', ' ', $view));
} else {
    http_response_code(404);
    $template = 'not-found';
    $pageTitle = 'Not found';
    $vars['missing'] = 'view: ' . $view;
}

$vars['view'] = $view;
$vars['pageTitle'] = $pageTitle;
$vars['navItems'] = $navItems;
$vars['navPrimary'] = $navPrimary;
$vars['navSecondary'] = $navSecondary;
$vars['bodyTemplate'] = $template;

render_view('layout', $vars);
