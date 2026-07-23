<?php
/**
 * The Functionality Map — the first thing a reader sees.
 *
 * Renders three things from one model (see build_functionality_map()):
 *   1. A slim hero (what this software does) + live, clickable statistics.
 *   2. An interactive, zoomable/pannable map built by JavaScript from the
 *      embedded JSON — the premium, "understand it in ten seconds" experience.
 *   3. An accessible, no-JavaScript fallback: nested areas that expand to their
 *      functionality and link straight into the generated documentation.
 *
 * The fallback is the source of truth for users without JavaScript and for
 * assistive technology; the interactive canvas is a progressive enhancement
 * layered on top of it. Both are the same data, so they can never disagree.
 *
 * @var Content $content
 * @var string $projectName
 */
$map = build_functionality_map($content);
$mapAreas = $map['areas'];
$mapStats = $map['stats'];
$mapContext = $map['context'];
$mapApp = $map['app'];
$mapLede = $mapApp['outcome'] !== '' ? $mapApp['outcome'] : $mapApp['summary'];
$mapJson = json_encode(
    $map,
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
);
?>
<section class="fmap" data-fmap aria-labelledby="fmap-h">
    <header class="fmap__intro reading-column">
        <p class="eyebrow">Functionality map</p>
        <h1 id="fmap-h"><?= h($projectName) ?></h1>
        <?php if ($mapLede !== ''): ?>
            <p class="lede"><?= h($mapLede) ?></p>
        <?php endif; ?>
        <p class="fmap__lead-note text-soft">A live map of what this software actually does — start here, then open any capability for its documentation.</p>
    </header>

    <?php if ($mapContext['active']): ?>
        <aside class="fmap-context" aria-label="Current AI work in context">
            <span class="fmap-context__pulse" aria-hidden="true"></span>
            <p class="fmap-context__body">
                <span class="fmap-context__label">Current context</span>
                <strong><?= h($mapContext['label']) ?></strong>
                — highlighted on the map below.
            </p>
        </aside>
    <?php endif; ?>

    <ul class="fmap-stats wide-section" aria-label="Repository at a glance">
        <?php foreach ($mapStats as $stat): ?>
            <li class="fmap-stat">
                <a class="fmap-stat__link" href="<?= h($stat['url']) ?>">
                    <span class="fmap-stat__value"><?= (int) $stat['value'] ?></span>
                    <span class="fmap-stat__label"><?= h($stat['label']) ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="fmap__frame wide-section">
        <p class="fmap__hint" data-fmap-hint hidden>
            <span>Drag to pan · scroll to zoom</span>
            <span>Click a capability to expand · double-click to open its docs</span>
        </p>

        <!-- The interactive canvas is created here by guide.js on capable
             screens. It starts empty and hidden so no-JS and small screens see
             the accessible fallback instead. -->
        <div class="fmap-stage" data-fmap-stage hidden aria-hidden="true"></div>

        <!-- Accessible fallback + mobile experience: areas as expandable cards
             that link straight into the documentation. Always in the DOM. -->
        <div class="fmap-fallback" data-fmap-fallback>
            <p class="fmap-fallback__intro text-soft">This map has <?= count($mapAreas) ?> major capabilit<?= count($mapAreas) === 1 ? 'y' : 'ies' ?>. Expand one to see what it includes, then open any item for its documentation.</p>
            <ol class="fmap-area-list">
                <?php foreach ($mapAreas as $area): ?>
                    <li class="fmap-area-card<?= $area['current'] ? ' is-current' : '' ?>" data-status="<?= h($area['status']) ?>">
                        <details class="fmap-area-card__details">
                            <summary class="fmap-area-card__summary">
                                <span class="fmap-area-card__head">
                                    <span class="fmap-dot" data-status="<?= h($area['status']) ?>" aria-hidden="true"></span>
                                    <span class="fmap-area-card__title"><?= h($area['title']) ?></span>
                                    <?php if ($area['current']): ?>
                                        <span class="fmap-tag fmap-tag--current">In context</span>
                                    <?php endif; ?>
                                    <span class="fmap-area-card__count"><?= (int) $area['count'] ?></span>
                                </span>
                                <?php if ($area['description'] !== ''): ?>
                                    <span class="fmap-area-card__desc"><?= h($area['description']) ?></span>
                                <?php endif; ?>
                            </summary>
                            <ul class="fmap-node-list">
                                <?php foreach ($area['children'] as $child): ?>
                                    <li class="fmap-node<?= $child['current'] ? ' is-current' : '' ?>">
                                        <a class="fmap-node__link" href="<?= h($child['url']) ?>">
                                            <span class="fmap-node__title">
                                                <span class="fmap-dot" data-status="<?= h($child['status']) ?>" aria-hidden="true"></span>
                                                <?= h($child['title']) ?>
                                            </span>
                                            <?php if ($child['summary'] !== ''): ?>
                                                <span class="fmap-node__summary"><?= h($child['summary']) ?></span>
                                            <?php endif; ?>
                                            <span class="fmap-node__meta">
                                                <span class="fmap-node__status"><?= h($child['statusLabel']) ?></span>
                                                <?php if ($child['fileCount'] > 0): ?>
                                                    <span class="fmap-node__files"><?= (int) $child['fileCount'] ?> file<?= $child['fileCount'] === 1 ? '' : 's' ?></span>
                                                <?php endif; ?>
                                                <span class="fmap-node__open">Open documentation →</span>
                                            </span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <p class="fmap-area-card__more">
                                <a class="text-link" href="<?= h($area['url']) ?>">View the <?= h($area['title']) ?> area →</a>
                            </p>
                        </details>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </div>

    <script type="application/json" data-fmap-model><?= $mapJson ?></script>
</section>
