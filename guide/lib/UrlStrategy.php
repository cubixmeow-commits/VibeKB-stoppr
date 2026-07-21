<?php

declare(strict_types=1);

/**
 * URL strategies let the *same* templates render in two environments without
 * maintaining two page systems:
 *
 *   - {@see DynamicUrlStrategy} — the PHP guide (Mode A). Query-string routing
 *     (`?view=...`) that works at the web root or in a subfolder with no
 *     rewrite rules, exactly as before.
 *   - {@see StaticUrlStrategy} — the static snapshot (Mode B). Path-based URLs
 *     (`functionality/app-startup.html`) emitted as **relative** links from the
 *     page currently being generated, so the output works on GitHub Pages and
 *     under any repository subpath.
 *
 * Templates call the free functions in `helpers.php` (`guide_url()`,
 * `functionality_url()`, `memory_url()`, `diagram_url()`, `guide_asset()`,
 * `site_root_url()`); those delegate to whichever strategy is current. The
 * dynamic behaviour is byte-for-byte the original logic.
 */
interface UrlStrategy
{
    /** URL for a view, e.g. view('functionality', ['id' => 'x']). */
    public function view(string $view, array $params = []): string;

    /** URL for a single functionality record. */
    public function functionality(string $id): string;

    /** URL for a memory record (decisions, warnings, ...). */
    public function memory(string $type, string $id): string;

    /** URL for the diagrams page, optionally anchored to a diagram id. */
    public function diagram(string $id = ''): string;

    /** URL for a guide asset relative path, e.g. "assets/css/guide.css". */
    public function asset(string $rel): string;

    /** URL of the site root (the parent of the guide directory). */
    public function siteRoot(): string;
}

/**
 * Mode A — the live PHP guide. Preserves the original query-string routing and
 * mtime asset cache-busting.
 */
final class DynamicUrlStrategy implements UrlStrategy
{
    private function base(): string
    {
        $script = $_SERVER['SCRIPT_NAME'] ?? '/guide/index.php';
        $dir = str_replace('\\', '/', dirname($script));
        return rtrim($dir, '/') . '/';
    }

    public function view(string $view, array $params = []): string
    {
        $query = [];
        if ($view !== '' && $view !== 'overview') {
            $query['view'] = $view;
        }
        foreach ($params as $key => $value) {
            $query[$key] = $value;
        }
        $base = $this->base();
        if ($query === []) {
            return $base;
        }
        return $base . '?' . http_build_query($query);
    }

    public function functionality(string $id): string
    {
        return $this->view('functionality', ['id' => $id]);
    }

    public function memory(string $type, string $id): string
    {
        return $this->view('why', ['type' => $type, 'id' => $id]);
    }

    public function diagram(string $id = ''): string
    {
        $url = $this->view('diagrams');
        return $id !== '' ? $url . '#' . $id : $url;
    }

    public function asset(string $rel): string
    {
        $rel = ltrim($rel, '/');
        $fsPath = dirname(__DIR__) . '/' . $rel; // guide/lib -> guide/
        $version = is_file($fsPath) ? (string) filemtime($fsPath) : '1';
        return $this->base() . $rel . '?v=' . $version;
    }

    public function siteRoot(): string
    {
        $base = $this->base();
        $parent = rtrim(str_replace('\\', '/', dirname(rtrim($base, '/'))), '/');
        return ($parent === '' ? '' : $parent) . '/';
    }
}

/**
 * Mode B — the static snapshot. Emits relative paths from the page currently
 * being generated. Deterministic: no mtime cache-busting in asset URLs.
 */
final class StaticUrlStrategy implements UrlStrategy
{
    /** Directory of the page being generated, relative to the docs root ("" = root). */
    private string $currentDir;

    public function __construct(string $currentDir = '')
    {
        $this->currentDir = trim($currentDir, '/');
    }

    /** Map a view + params to a docs-root-relative output path (may include #frag). */
    public function pathForView(string $view, array $params = []): string
    {
        switch ($view) {
            case '':
            case 'overview':
                return 'index.html';
            case 'functionality':
                if (isset($params['id']) && $params['id'] !== '') {
                    return 'functionality/' . $this->slug((string) $params['id']) . '.html';
                }
                if (isset($params['area']) && $params['area'] !== '') {
                    return 'functionality/index.html#grp-' . $this->slug((string) $params['area']);
                }
                return 'functionality/index.html';
            case 'why':
                if (isset($params['type'], $params['id']) && $params['type'] !== '' && $params['id'] !== '') {
                    return 'why/' . $this->slug((string) $params['type']) . '/' . $this->slug((string) $params['id']) . '.html';
                }
                return 'why/index.html';
            case 'diagrams':
                return 'diagrams/index.html';
            case 'search':
                return 'search/index.html';
            default:
                return $this->slug($view) . '/index.html';
        }
    }

    public function view(string $view, array $params = []): string
    {
        return $this->rel($this->pathForView($view, $params));
    }

    public function functionality(string $id): string
    {
        return $this->rel('functionality/' . $this->slug($id) . '.html');
    }

    public function memory(string $type, string $id): string
    {
        return $this->rel('why/' . $this->slug($type) . '/' . $this->slug($id) . '.html');
    }

    public function diagram(string $id = ''): string
    {
        $path = 'diagrams/index.html' . ($id !== '' ? '#' . $this->slug($id) : '');
        return $this->rel($path);
    }

    public function asset(string $rel): string
    {
        return $this->rel(ltrim($rel, '/'));
    }

    public function siteRoot(): string
    {
        return $this->rel('index.html');
    }

    /** Compute a relative link from the current page's directory to a docs-root path. */
    private function rel(string $path): string
    {
        [$target, $frag] = array_pad(explode('#', $path, 2), 2, null);
        $depth = $this->currentDir === '' ? 0 : substr_count($this->currentDir, '/') + 1;
        $prefix = str_repeat('../', $depth);
        $out = $prefix . ltrim((string) $target, '/');
        if ($out === '') {
            $out = './';
        }
        return $frag !== null ? $out . '#' . $frag : $out;
    }

    private function slug(string $value): string
    {
        return (string) preg_replace('/[^a-z0-9\-]/i', '', $value);
    }
}
