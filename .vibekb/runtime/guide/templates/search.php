<?php
/**
 * Search — client-side search over a generated index. Works in both modes:
 * the dynamic guide fetches the index live at `?view=search&data=json`; the
 * static snapshot fetches the generated `assets/data/search.json`. No external
 * service, no network dependency, and the guide remains readable without it.
 *
 * @var Content $content
 */
$generation = $GLOBALS['vibekb_generation'] ?? ['mode' => 'dynamic'];
$searchIndexUrl = ($generation['mode'] ?? 'dynamic') === 'static'
    ? guide_asset('assets/data/search.json')
    : guide_url('search', ['data' => 'json']);
?>
<article class="view view-doc">
    <header class="page-head reading-column">
        <p class="eyebrow">Search</p>
        <h1>Search the guide</h1>
        <p class="lede">Find functionality, functional areas, files, data &amp; storage, warnings, decisions, changes, diagrams, and work records.</p>
        <noscript><p class="callout callout--warn">Search needs JavaScript. The rest of the guide reads fine without it — use the navigation to browse.</p></noscript>
    </header>

    <form class="filters wide-section" role="search" onsubmit="return false;">
        <label>Query
            <input type="search" id="search-query" name="q" placeholder="Type to search…" autocomplete="off">
        </label>
    </form>

    <div id="search-results" class="wide-section" data-search-index="<?= h($searchIndexUrl) ?>" aria-live="polite"></div>
    <p class="empty-state" id="search-empty" hidden>No results found. Try a shorter or different term.</p>
</article>
