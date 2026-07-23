<?php

declare(strict_types=1);

/**
 * Shared helpers for the VibeKB V1 guide: escaping, routing, and the
 * controlled vocabularies the content model validates against.
 */

require_once __DIR__ . '/UrlStrategy.php';

/** HTML-escape a value for output. */
function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * The current URL strategy. Pass a strategy to switch modes (the static
 * generator does this once per page); call with no argument to read it. The
 * dynamic strategy is the default so the PHP guide behaves exactly as before.
 */
function guide_url_strategy(?UrlStrategy $set = null): UrlStrategy
{
    static $current = null;
    if ($set !== null) {
        $current = $set;
    }
    if ($current === null) {
        $current = new DynamicUrlStrategy();
    }
    return $current;
}

/**
 * Cache-busting/relative URL for a guide asset. In dynamic mode this appends
 * the file's modification time as `?v=` (no build step needed); in static mode
 * it returns a deterministic relative path.
 *
 * @param string $rel path relative to the guide directory, e.g. "assets/css/guide.css"
 */
function guide_asset(string $rel): string
{
    return guide_url_strategy()->asset($rel);
}

/** URL of the site root (parent of the guide directory). */
function site_root_url(): string
{
    return guide_url_strategy()->siteRoot();
}

/**
 * Build an in-app URL for a view.
 *
 * @param array<string, string> $params
 */
function guide_url(string $view = '', array $params = []): string
{
    return guide_url_strategy()->view($view, $params);
}

/** URL for a single functionality record. */
function functionality_url(string $id): string
{
    return guide_url_strategy()->functionality($id);
}

/** URL for a memory record (decision, warning, assumption, ...). */
function memory_url(string $type, string $id): string
{
    return guide_url_strategy()->memory($type, $id);
}

/** URL for the diagrams page, optionally anchored to a diagram id. */
function diagram_url(string $id = ''): string
{
    return guide_url_strategy()->diagram($id);
}

/** URL for a specific explainable node/edge anchor on the diagrams page. */
function diagram_anchor_url(string $anchor): string
{
    $base = guide_url_strategy()->diagram('');
    return $anchor === '' ? $base : $base . '#' . $anchor;
}

/** Allowed functionality statuses. */
function status_vocabulary(): array
{
    return [
        'implemented' => 'Implemented',
        'partial' => 'Partially implemented',
        'planned' => 'Planned',
        'experimental' => 'Experimental',
        'disabled' => 'Disabled',
        'deprecated' => 'Deprecated',
        'broken' => 'Broken',
        'unknown' => 'Unknown',
        'needs-verification' => 'Needs verification',
    ];
}

/** Allowed verification / provenance states. */
function verification_vocabulary(): array
{
    return [
        'verified-by-test' => 'Verified by test',
        'verified-manually' => 'Verified manually',
        'verified-from-source' => 'Verified from source',
        'inferred-from-source' => 'Inferred from source',
        'reported-by-developer' => 'Reported by developer',
        'not-verified' => 'Not verified',
        'verification-failed' => 'Verification failed',
        'superseded' => 'Superseded',
        'contradicted' => 'Contradicted',
    ];
}

/**
 * Supported diagram types. These are the *kinds* of diagram VibeKB knows how to
 * present — they are not mandatory diagrams for every repository. A repository
 * ships only the diagrams it can ground in source.
 */
function diagram_type_vocabulary(): array
{
    return [
        'application-overview' => 'Application overview',
        'user-journey' => 'User journey',
        'startup-flow' => 'Startup flow',
        'authentication-flow' => 'Authentication flow',
        'access-flow' => 'Subscription / access flow',
        'navigation-map' => 'Main navigation',
        'feature-access' => 'Feature access',
        'request-flow' => 'Request flow',
        'data-flow' => 'Data flow',
        'storage-map' => 'Storage map',
        'external-services' => 'External services',
        'code-architecture' => 'Code architecture',
        'state-management' => 'State management',
        'risk-and-uncertainty-map' => 'Risk and uncertainty map',
    ];
}

function diagram_type_label(string $type): string
{
    return diagram_type_vocabulary()[$type] ?? ucfirst(str_replace('-', ' ', $type));
}

/**
 * Controlled edge-mechanism vocabulary for explainable diagrams.
 *
 * An edge may be drawn only when a concrete mechanism can be stated in one
 * sentence. This is the single canonical source used by loading, validation,
 * and documentation. Machine value (stable, lowercase) => readable label.
 *
 * Vague pseudo-mechanisms (relates-to, works-with, interacts-with,
 * associated-with, connected-to) are deliberately absent; the validator rejects
 * anything not listed here so a coincidence can never masquerade as a mechanism.
 */
function edge_mechanism_vocabulary(): array
{
    return [
        'calls' => 'calls',
        'delegates-to' => 'delegates to',
        'reads' => 'reads',
        'writes' => 'writes',
        'configures' => 'configures',
        'instantiates' => 'instantiates',
        'depends-on' => 'depends on',
        'emits' => 'emits',
        'listens-to' => 'listens to',
        'validates' => 'validates',
        'stores-in' => 'stores in',
        'retrieves-from' => 'retrieves from',
        'creates' => 'creates',
        'updates' => 'updates',
        'deletes' => 'deletes',
        'routes-to' => 'routes to',
        'renders' => 'renders',
        'returns-to' => 'returns to',
        'sends-to' => 'sends to',
        'receives-from' => 'receives from',
    ];
}

function edge_mechanism_label(string $mechanism): string
{
    return edge_mechanism_vocabulary()[$mechanism] ?? str_replace('-', ' ', $mechanism);
}

/**
 * Controlled file-role vocabulary for files shown in node/edge explanations.
 * A file with a reason is knowledge; a bare path is browsing. Roles keep the
 * curated file list legible and comparable across diagrams.
 */
function file_role_vocabulary(): array
{
    return [
        'primary implementation' => 'Primary implementation',
        'entry point' => 'Entry point',
        'caller' => 'Caller',
        'callee' => 'Callee',
        'dependency' => 'Dependency',
        'configuration' => 'Configuration',
        'data model' => 'Data model',
        'storage' => 'Storage',
        'renderer' => 'Renderer',
        'route definition' => 'Route definition',
        'validation' => 'Validation',
        'integration adapter' => 'Integration adapter',
        'supporting utility' => 'Supporting utility',
        'test or verification evidence' => 'Test / verification evidence',
    ];
}

function file_role_label(string $role): string
{
    return file_role_vocabulary()[$role] ?? ucfirst(str_replace('-', ' ', $role));
}

/**
 * The two edge verification states used by V1 explainable diagrams. Verified
 * edges render solid; inferred edges render dashed. Line style is never the only
 * signal — the state is always stated in text too. These reuse the shared
 * verification vocabulary so a topology can also carry the other honest states
 * (e.g. not-verified) without inventing a parallel scheme.
 */
function edge_verification_is_verified(string $v): bool
{
    return in_array($v, ['verified-by-test', 'verified-manually', 'verified-from-source'], true);
}

/**
 * Build a stable, immutable external source link for a repository-relative file
 * path using the recorded source provenance. Prefers the analysed commit so the
 * link cannot drift; falls back to the branch. Returns null (and callers show the
 * bare path) when a reliable URL cannot be produced — a link is never fabricated.
 *
 * Line numbers are appended only when explicitly supplied (genuinely traced);
 * this function never invents them.
 *
 * @param array<string, string|bool> $provenance Normalised provenance (see provenance_data()).
 */
function source_link_url(array $provenance, string $repoRelPath, string $lineRange = ''): ?string
{
    $repo = trim((string) ($provenance['source_repository'] ?? ''));
    $path = ltrim(str_replace('\\', '/', trim($repoRelPath)), '/');
    if ($repo === '' || $path === '' || str_contains($path, '..')) {
        return null;
    }

    // A commit is immutable; prefer it. Extract the bare hash from a value like
    // "c1617ab (2026-07-16)". Fall back to the branch if no commit is recorded.
    $ref = '';
    $commit = (string) ($provenance['source_commit'] ?? '');
    if ($commit !== '' && preg_match('/[0-9a-f]{7,40}/i', $commit, $m)) {
        $ref = $m[0];
    }
    if ($ref === '') {
        $ref = trim((string) ($provenance['source_branch'] ?? ''));
    }
    if ($ref === '') {
        return null;
    }

    $subpath = trim(str_replace('\\', '/', (string) ($provenance['source_subpath'] ?? '')), '/');
    $fullPath = $subpath !== '' ? $subpath . '/' . $path : $path;

    // Only GitHub-style blob URLs are generated in V1; other hosts get no link.
    $host = rtrim($repo, '/');
    if (!preg_match('#^https?://[^/]*github\.com/[^/]+/[^/]+#i', $host)) {
        return null;
    }
    $host = preg_replace('#\.git$#', '', $host);

    $encodedRef = rawurlencode($ref);
    $encodedPath = implode('/', array_map('rawurlencode', explode('/', $fullPath)));
    $url = $host . '/blob/' . $encodedRef . '/' . $encodedPath;
    if ($lineRange !== '' && preg_match('/^L\d+(-L\d+)?$/', $lineRange)) {
        $url .= '#' . $lineRange;
    }
    return $url;
}

/** Allowed file safety levels. */
function safety_vocabulary(): array
{
    return [
        'presentation-only' => 'Presentation only',
        'low-impact' => 'Low impact',
        'moderate-impact' => 'Moderate impact',
        'understand-dependencies-first' => 'Understand dependencies first',
        'high-impact' => 'High impact',
        'generated-or-managed' => 'Generated / managed elsewhere',
        'unknown' => 'Unknown',
    ];
}

function status_label(string $status): string
{
    return status_vocabulary()[$status] ?? ucfirst(str_replace('-', ' ', $status));
}

function verification_label(string $v): string
{
    return verification_vocabulary()[$v] ?? ucfirst(str_replace('-', ' ', $v));
}

function safety_label(string $s): string
{
    return safety_vocabulary()[$s] ?? ucfirst(str_replace('-', ' ', $s));
}

/** CSS modifier class for a status badge. */
function status_tone(string $status): string
{
    return match ($status) {
        'implemented' => 'ok',
        'partial', 'experimental', 'needs-verification' => 'warn',
        'planned' => 'info',
        'disabled', 'deprecated' => 'muted',
        'broken' => 'danger',
        default => 'unknown',
    };
}

/** CSS modifier class for a verification badge. */
function verification_tone(string $v): string
{
    return match ($v) {
        'verified-by-test', 'verified-manually', 'verified-from-source' => 'ok',
        'inferred-from-source', 'reported-by-developer' => 'info',
        'not-verified', 'needs-verification' => 'warn',
        'verification-failed', 'contradicted', 'superseded' => 'danger',
        default => 'unknown',
    };
}

/** CSS modifier class for a file safety badge. */
function safety_tone(string $s): string
{
    return match ($s) {
        'presentation-only', 'low-impact' => 'ok',
        'moderate-impact' => 'info',
        'understand-dependencies-first' => 'warn',
        'high-impact' => 'danger',
        default => 'unknown',
    };
}

/** CSS modifier class for a memory-record severity. */
function severity_tone(string $s): string
{
    return match (strtolower($s)) {
        'critical' => 'danger',
        'high' => 'danger',
        'medium' => 'warn',
        'low' => 'info',
        default => 'unknown',
    };
}

/**
 * Compact, non-navigable repository-location tree for a node's files. It answers
 * "where does this live?" — it is not a directory browser, so it only ever
 * contains the given paths. Rendered as a nested list styled to read like a tree.
 *
 * @param list<string> $paths repository-relative file paths
 */
function diagram_location_tree(array $paths, string $location = ''): string
{
    $root = [];
    foreach ($paths as $p) {
        $parts = array_values(array_filter(explode('/', str_replace('\\', '/', (string) $p)), fn ($x) => $x !== ''));
        $ref = &$root;
        foreach ($parts as $part) {
            if (!isset($ref[$part])) {
                $ref[$part] = [];
            }
            $ref = &$ref[$part];
        }
        unset($ref);
    }
    if ($root === []) {
        if ($location === '') {
            return '';
        }
        // No files: show the bare folder path as the location answer.
        $segments = array_filter(explode('/', trim(str_replace('\\', '/', $location), '/')), fn ($x) => $x !== '');
        $inner = '';
        foreach (array_reverse($segments) as $seg) {
            $inner = '<ul class="loc-tree"><li><span class="loc-dir">' . h($seg) . '/</span>' . $inner . '</li></ul>';
        }
        return $inner;
    }

    $render = function (array $level) use (&$render): string {
        $out = '';
        foreach ($level as $name => $children) {
            if ($children === []) {
                $out .= '<li><code class="loc-file">' . h((string) $name) . '</code></li>';
            } else {
                $out .= '<li><span class="loc-dir">' . h((string) $name) . '/</span><ul>' . $render($children) . '</ul></li>';
            }
        }
        return $out;
    };
    return '<ul class="loc-tree">' . $render($root) . '</ul>';
}

/** A clear "show me the implementation" label appropriate to a file's role. */
function source_link_label(string $role): string
{
    return match ($role) {
        'primary implementation' => 'Open implementation',
        'caller' => 'View caller',
        'callee' => 'View callee',
        'configuration' => 'View configuration',
        'route definition' => 'View routes',
        'entry point' => 'Open entry point',
        default => 'View source on GitHub',
    };
}

/**
 * Render one file, with reason, as a knowledge item: role, path, an external
 * source link (the terminal "show me the implementation"), the diagram-specific
 * reason, and the canonical purpose when the file is a known important file.
 *
 * @param array<string, mixed> $file resolved file (see Content::resolvedTopology)
 */
function diagram_file_item(array $file): string
{
    $path = (string) ($file['path'] ?? '');
    $role = (string) ($file['role'] ?? '');
    $reason = (string) ($file['reason'] ?? '');
    $url = $file['url'] ?? null;
    $canon = (string) ($file['canonical_purpose'] ?? '');

    $out = '<li class="dx-file">';
    $out .= '<div class="dx-file__head">';
    if ($role !== '') {
        $out .= '<span class="dx-file__role">' . h(file_role_label($role)) . '</span>';
    }
    $out .= '<code class="dx-file__path">' . h($path) . '</code>';
    $out .= '</div>';
    if ($reason !== '') {
        $out .= '<p class="dx-file__reason">' . h($reason) . '</p>';
    }
    if ($canon !== '' && $canon !== $reason) {
        $out .= '<p class="dx-file__canon text-soft"><span class="dx-file__canon-label">Canonical purpose:</span> ' . h($canon) . '</p>';
    }
    if (is_string($url) && $url !== '') {
        $out .= '<p class="dx-file__link"><a href="' . h($url) . '" rel="noopener noreferrer">' . h(source_link_label($role)) . ' <span aria-hidden="true">↗</span></a></p>';
    } else {
        $out .= '<p class="dx-file__link muted">No reliable source link — repository path shown above.</p>';
    }
    $out .= '</li>';
    return $out;
}

/**
 * The verification badge for a diagram edge, plus the line-style word so colour
 * and line style are never the only signal.
 */
function edge_verification_badge(string $verification): string
{
    $verified = edge_verification_is_verified($verification);
    $line = $verified ? 'Solid line' : 'Dashed line';
    return verification_badge($verification === '' ? 'not-verified' : $verification)
        . ' <span class="badge badge--' . ($verified ? 'ok' : 'info') . '">' . h($line) . '</span>';
}

/**
 * Render a template file with the given variables. Templates receive $vars
 * as extracted locals plus the shared $app context.
 *
 * @param array<string, mixed> $vars
 */
function render_view(string $template, array $vars): void
{
    $file = dirname(__DIR__) . '/templates/' . $template . '.php';
    if (!is_file($file)) {
        http_response_code(500);
        echo 'View template missing.';
        return;
    }
    extract($vars, EXTR_SKIP);
    require $file;
}

/** Render a status badge chip. */
function badge(string $label, string $tone): string
{
    return '<span class="badge badge--' . h($tone) . '">' . h($label) . '</span>';
}

/** Status badge for a functionality status value. */
function status_badge(string $status): string
{
    return badge(status_label($status), status_tone($status));
}

/** Verification badge for a verification/provenance value. */
function verification_badge(string $v): string
{
    return badge(verification_label($v), verification_tone($v));
}

/**
 * Render a list of resolved functionality links as chips.
 *
 * @param list<array{id: string, title: string, resolved: bool}> $items
 */
function functionality_chips(array $items): string
{
    if ($items === []) {
        return '<span class="muted">None.</span>';
    }
    $out = [];
    foreach ($items as $it) {
        if ($it['resolved']) {
            $out[] = '<a class="chip" href="' . h(functionality_url($it['id'])) . '">' . h($it['title']) . '</a>';
        } else {
            $out[] = '<span class="chip chip--broken" title="Unresolved reference">' . h($it['title']) . ' ⚠</span>';
        }
    }
    return implode(' ', $out);
}

/**
 * Render a list of resolved memory references as chips.
 *
 * @param list<array{type: string, id: string, title: string, resolved: bool}> $items
 */
function memory_chips(array $items): string
{
    if ($items === []) {
        return '<span class="muted">None.</span>';
    }
    $out = [];
    foreach ($items as $it) {
        if ($it['resolved']) {
            $out[] = '<a class="chip" href="' . h(memory_url($it['type'], $it['id'])) . '">' . h($it['title']) . '</a>';
        } else {
            $out[] = '<span class="chip chip--broken" title="Unresolved reference">' . h($it['title']) . ' ⚠</span>';
        }
    }
    return implode(' ', $out);
}

/** Render a list of file paths as monospace chips. */
function file_chips(mixed $paths): string
{
    $list = is_array($paths) ? $paths : ($paths === null || $paths === '' ? [] : [$paths]);
    if ($list === []) {
        return '<span class="muted">None.</span>';
    }
    $out = [];
    foreach ($list as $p) {
        $out[] = '<code class="chip chip--file">' . h((string) $p) . '</code>';
    }
    return implode(' ', $out);
}

/*
 * Count vocabulary.
 *
 * VibeKB counts three different things and they must never be conflated:
 *   - functional AREAS  — grouped product categories (functionality/index.json).
 *   - functionality RECORDS — individual behaviours (functionality/records/*.md).
 *   - STATUS counts — records tallied by lifecycle status.
 * Every total the interface prints must identify its unit. These helpers make
 * that the default path so a contradictory or unit-less total is hard to ship.
 */

/** "N functionality records across M functional areas". */
function count_records_phrase(int $records, int $areas): string
{
    return $records . ' functionality record' . ($records === 1 ? '' : 's')
        . ' across ' . $areas . ' functional area' . ($areas === 1 ? '' : 's');
}

/**
 * Render the status tally as unit-labelled badges (e.g. "Implemented · 23").
 *
 * @param array<string, int> $statusCounts
 */
function status_count_badges(array $statusCounts): string
{
    $out = [];
    foreach (status_vocabulary() as $key => $label) {
        if (!empty($statusCounts[$key])) {
            $out[] = badge($label . ' · ' . $statusCounts[$key], status_tone($key));
        }
    }
    return $out === [] ? '<span class="muted">No records yet.</span>' : implode(' ', $out);
}
