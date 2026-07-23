<?php

declare(strict_types=1);

/**
 * The Functionality Map model.
 *
 * VibeKB's promise is "understand what your software is doing" — so before any
 * documentation, the guide shows a high-level, interactive map of the
 * application's functionality. This builder turns the *existing* living model
 * into that map's data. It invents nothing: areas are the functionality groups,
 * nodes are functionality records, and every "open documentation" link is the
 * same detail page the rest of the guide renders.
 *
 * Three progressive levels of understanding:
 *   Level 1 — functional areas (major capabilities), ~one screen.
 *   Level 2 — the functionality records inside an area (still human-readable).
 *   Level 3 — the generated documentation the guide already produces.
 *
 * The returned structure is consumed twice: server-side to render an accessible,
 * no-JavaScript fallback (nested lists that link straight into the docs), and
 * client-side (embedded as JSON) to render the interactive, zoomable map. Both
 * modes (dynamic guide and static /docs) share this builder, so the map can
 * never disagree with the rest of the guide.
 *
 * URLs are produced through the active {@see UrlStrategy}, so links resolve
 * correctly whether the page is served dynamically or from a static subpath.
 */

/**
 * Build the Functionality Map from the living model.
 *
 * @return array{
 *   app: array{name: string, outcome: string, summary: string},
 *   areas: list<array<string, mixed>>,
 *   stats: list<array{key: string, label: string, value: int, url: string}>,
 *   context: array{active: bool, label: string, ids: list<string>}
 * }
 */
function build_functionality_map(Content $content): array
{
    $identity = $content->projectDoc('identity');
    $appName = (string) ($identity['meta']['title'] ?? 'This software');
    $appOutcome = (string) ($identity['meta']['primary_outcome'] ?? '');
    $appSummary = (string) ($identity['meta']['one_liner'] ?? $identity['meta']['summary'] ?? '');

    // ---- current context (which nodes an agent is actively working on) -------
    // Architected so `php tools/vibekb.php context` can feed this later: today it
    // reads the recorded current work; the shape stays the same when that becomes
    // a richer, tool-driven signal.
    $work = $content->currentWork();
    $affected = $work !== null ? $content->asList($work['meta']['affected_functionality'] ?? []) : [];
    $workStatus = (string) ($work['meta']['status'] ?? 'idle');
    $contextActive = $affected !== [] && !in_array($workStatus, ['', 'idle'], true);
    $affectedSet = array_fill_keys($affected, true);
    $contextLabel = $contextActive
        ? (string) ($work['meta']['title'] ?? 'Current work')
        : '';

    // ---- Level 1 (areas) + Level 2 (functionality records) -------------------
    $areas = [];
    foreach ($content->functionalityGroups() as $group) {
        $children = [];
        $areaStatuses = [];
        $areaHasContext = false;

        foreach ($group['records'] as $rec) {
            $m = $rec['meta'];
            $id = (string) ($m['id'] ?? '');
            if ($id === '') {
                continue;
            }
            $status = (string) ($m['status'] ?? 'unknown');
            $areaStatuses[$status] = ($areaStatuses[$status] ?? 0) + 1;

            $inContext = isset($affectedSet[$id]);
            $areaHasContext = $areaHasContext || $inContext;

            // Level-3 reach: the important files that implement this capability.
            // Shown as the node's "child count" and its cue that documentation
            // lies one level deeper.
            $fileCount = count($content->filesForFunctionality($id));
            $depCount = count($content->asList($m['depends_on'] ?? []));

            $children[] = [
                'id' => $id,
                'title' => (string) ($m['title'] ?? $id),
                'summary' => (string) ($m['summary'] ?? ''),
                'status' => $status,
                'statusLabel' => status_label($status),
                'verification' => (string) ($m['verification'] ?? 'not-verified'),
                'verificationLabel' => verification_label((string) ($m['verification'] ?? 'not-verified')),
                'userFacing' => (bool) ($m['user_facing'] ?? false),
                'fileCount' => $fileCount,
                'depCount' => $depCount,
                'url' => functionality_url($id),
                'current' => $inContext,
            ];
        }

        if ($children === []) {
            continue;
        }

        $areas[] = [
            'id' => (string) $group['id'],
            'title' => (string) $group['title'],
            'description' => (string) $group['description'],
            'count' => count($children),
            'status' => map_aggregate_status($areaStatuses),
            'statusCounts' => $areaStatuses,
            'url' => guide_url('functionality', ['area' => (string) $group['id']]),
            'current' => $areaHasContext && $contextActive,
            'children' => $children,
        ];
    }

    // ---- live statistics (every tile links into the guide) -------------------
    $relationshipCount = 0;
    foreach ($content->allFunctionality() as $rec) {
        $relationshipCount += count($content->asList($rec['meta']['depends_on'] ?? []));
    }

    $stats = [
        [
            'key' => 'functionality',
            'label' => 'Functionalities',
            'value' => count($content->allFunctionality()),
            'url' => guide_url('functionality'),
        ],
        [
            'key' => 'areas',
            'label' => 'Functional areas',
            'value' => count($areas),
            'url' => guide_url('functionality'),
        ],
        [
            'key' => 'systems',
            'label' => 'Systems',
            'value' => count($content->systemDocs()),
            'url' => guide_url('how-it-works'),
        ],
        [
            'key' => 'files',
            'label' => 'Key files',
            'value' => count($content->files()),
            'url' => guide_url('files'),
        ],
        [
            'key' => 'relationships',
            'label' => 'Relationships',
            'value' => $relationshipCount,
            'url' => guide_url('functionality'),
        ],
        [
            'key' => 'diagrams',
            'label' => 'Diagrams',
            'value' => count($content->allDiagrams()),
            'url' => guide_url('diagrams'),
        ],
    ];

    return [
        'app' => [
            'name' => $appName,
            'outcome' => $appOutcome,
            'summary' => $appSummary,
        ],
        'areas' => $areas,
        'stats' => $stats,
        'context' => [
            'active' => $contextActive,
            'label' => $contextLabel,
            'ids' => array_values($affected),
        ],
    ];
}

/**
 * Reduce an area's per-status counts to a single representative status for the
 * node's health dot. Prefers the most attention-worthy state so a broken or
 * unverified capability is never hidden behind a healthy majority.
 *
 * @param array<string, int> $counts
 */
function map_aggregate_status(array $counts): string
{
    if ($counts === []) {
        return 'unknown';
    }
    // Severity order: anything needing attention wins over "implemented".
    $priority = ['broken', 'deprecated', 'disabled', 'needs-verification', 'experimental', 'planned', 'partial', 'unknown', 'implemented'];
    foreach ($priority as $status) {
        if (!empty($counts[$status])) {
            return $status;
        }
    }
    return (string) array_key_first($counts);
}
