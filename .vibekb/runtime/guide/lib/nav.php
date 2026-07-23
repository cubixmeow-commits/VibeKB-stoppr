<?php

declare(strict_types=1);

/**
 * Shared navigation, route, and page-title definitions.
 *
 * Both output modes consume these so the dynamic PHP guide (index.php) and the
 * static snapshot generator (tools/generate-static.php) present the exact same
 * page inventory and chrome. Add a view once here and both modes pick it up.
 */

/**
 * View -> body template. `functionality` is null because it is special-cased
 * (index vs detail depending on the record id).
 *
 * @return array<string, string|null>
 */
function guide_routes(): array
{
    return [
        'overview' => 'overview',
        'functionality' => null,
        'how-it-works' => 'how-it-works',
        'diagrams' => 'diagrams',
        'data' => 'data',
        'files' => 'files',
        'current-work' => 'current-work',
        'changes' => 'changes',
        'why' => 'why',
        'handoff' => 'handoff',
        'reference' => 'reference',
        'search' => 'search',
    ];
}

/**
 * Primary navigation — answers the product questions in order.
 *
 * @return list<array{view: string, label: string}>
 */
function guide_nav_primary(): array
{
    return [
        ['view' => 'overview', 'label' => 'Overview'],
        ['view' => 'functionality', 'label' => 'Functionality'],
        ['view' => 'how-it-works', 'label' => 'Architecture'],
        ['view' => 'diagrams', 'label' => 'Diagrams'],
        ['view' => 'current-work', 'label' => 'Current work'],
    ];
}

/**
 * Secondary navigation — deeper exploration.
 *
 * @return list<array{view: string, label: string}>
 */
function guide_nav_secondary(): array
{
    return [
        ['view' => 'data', 'label' => 'Data &amp; storage'],
        ['view' => 'files', 'label' => 'Files that matter'],
        ['view' => 'changes', 'label' => 'Changes'],
        ['view' => 'why', 'label' => 'Decisions &amp; rationale'],
        ['view' => 'handoff', 'label' => 'AI handoff'],
        ['view' => 'reference', 'label' => 'Reference'],
    ];
}

/**
 * View -> page title.
 *
 * @return array<string, string>
 */
function guide_page_titles(): array
{
    return [
        'overview' => 'Overview',
        'functionality' => 'Functionality',
        'how-it-works' => 'Architecture',
        'diagrams' => 'Diagrams',
        'data' => 'Data & storage',
        'files' => 'Files that matter',
        'current-work' => 'Current work',
        'changes' => 'Changes',
        'why' => 'Decisions & rationale',
        'handoff' => 'AI handoff',
        'reference' => 'Reference',
        'search' => 'Search',
    ];
}
