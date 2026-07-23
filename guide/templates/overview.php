<?php
/**
 * Software Overview — the first screen.
 *
 * The centerpiece is the interactive Functionality Map (rendered from the living
 * model, see partials/functionality-map.php): a reader should understand what
 * the software does before reading a single documentation page. The written
 * sections below the map are the progressive, Level-3 detail — the "read more"
 * once the map has given the mental model.
 *
 * @var Content $content
 * @var string $projectName
 */
$identity = $content->projectDoc('identity');
$currentState = $content->projectDoc('current-state');
$warnings = $content->memoryByType('warnings');
$work = $content->currentWork();
$handoff = $content->handoff();
$mentalModel = $content->systemDoc('mental-model');
$identitySummary = (string) ($identity['meta']['summary'] ?? '');
$currentSummary = (string) ($currentState['meta']['summary'] ?? '');
$generation = $GLOBALS['vibekb_generation'] ?? ['mode' => 'dynamic'];
$provenance = provenance_data($content->manifest(), $generation);
$example = is_array($content->manifest()['example_project'] ?? null) ? $content->manifest()['example_project'] : [];
$exampleRepo = (string) ($example['source_repository'] ?? '');
$exampleName = (string) ($example['name'] ?? $projectName);
?>
<article class="view view-overview">

    <?php require __DIR__ . '/partials/functionality-map.php'; ?>

    <aside class="source-notice reading-column" aria-label="Example source notice">
        <p class="source-notice__title">Example snapshot: <?= h($exampleName) ?></p>
        <p class="source-notice__hint">Derived from source · Reverify before editing functionality claims</p>
        <details class="source-notice__details">
            <summary>About this example source</summary>
            <p>
                <?= h($projectName) ?> is the real application VibeKB is explaining here — it is not bundled into
                VibeKB. This model was derived read-only from the source
                <?php if ($exampleRepo !== ''): ?>
                    (<a href="<?= h($exampleRepo) ?>" rel="noopener noreferrer"><?= h($exampleRepo) ?></a>)
                <?php endif; ?>
                and can go stale; re-verify against the source before changing any functionality claim.
            </p>
            <p><a class="text-link" href="<?= h(guide_url('reference')) ?>">See Reference for content model details →</a></p>
        </details>
    </aside>

    <?= provenance_panel($provenance) ?>

    <section class="content-section reading-column" aria-labelledby="ov-what">
        <header class="section-intro">
            <h2 id="ov-what">What this software does</h2>
        </header>
        <?php if ($identitySummary !== ''): ?>
            <p><?= h($identitySummary) ?></p>
        <?php endif; ?>
        <?php if ($currentSummary !== '' && $currentSummary !== $identitySummary): ?>
            <p class="text-soft"><?= h($currentSummary) ?></p>
        <?php endif; ?>
        <p><a class="text-link" href="<?= h(guide_url('functionality')) ?>">Browse the full functionality index →</a></p>
    </section>

    <section class="content-section reading-column" aria-labelledby="ov-think">
        <header class="section-intro">
            <h2 id="ov-think">How to think about it</h2>
        </header>
        <?php if ($mentalModel !== null && ($mentalModel['meta']['summary'] ?? '') !== ''): ?>
            <p><?= h((string) $mentalModel['meta']['summary']) ?></p>
        <?php else: ?>
            <p class="muted">No mental model recorded yet.</p>
        <?php endif; ?>
        <p><a class="text-link" href="<?= h(guide_url('how-it-works')) ?>">Read the architecture →</a></p>
    </section>

    <div class="split content-section">
        <section aria-labelledby="ov-warn" class="callout callout--warn">
            <h2 id="ov-warn">Active warnings</h2>
            <?php if ($warnings === []): ?>
                <p class="muted">No active warnings recorded.</p>
            <?php else: ?>
                <ul class="callout-list">
                    <?php foreach ($warnings as $wid => $w): ?>
                        <li>
                            <a href="<?= h(memory_url('warnings', (string) $wid)) ?>"><?= h((string) ($w['meta']['title'] ?? $wid)) ?></a>
                            <?= badge(ucfirst((string) ($w['meta']['severity'] ?? 'severity')), severity_tone((string) ($w['meta']['severity'] ?? ''))) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

        <section aria-labelledby="ov-work" class="callout callout--work">
            <h2 id="ov-work">Current AI work</h2>
            <?php if ($work !== null): ?>
                <p><strong><?= h((string) ($work['meta']['title'] ?? 'Current work')) ?></strong>
                   <?= badge(str_replace('-', ' ', (string) ($work['meta']['status'] ?? 'unknown')), 'info') ?></p>
                <p><?= h((string) ($work['meta']['summary'] ?? '')) ?></p>
                <p><a class="text-link" href="<?= h(guide_url('current-work')) ?>">See current work →</a></p>
            <?php else: ?>
                <p class="muted">No active AI work recorded.</p>
            <?php endif; ?>
        </section>
    </div>

    <section class="next-step content-section" aria-labelledby="ov-next">
        <h2 id="ov-next">Recommended starting point</h2>
        <?php $next = $handoff['meta']['summary'] ?? ''; ?>
        <p><?= h((string) ($next !== '' ? $next : 'Start with the functionality index, then read the architecture.')) ?></p>
        <p class="button-row">
            <a class="btn btn--primary" href="<?= h(guide_url('functionality')) ?>">Explore functionality</a>
            <a class="btn" href="<?= h(guide_url('handoff')) ?>">Read the AI handoff</a>
        </p>
    </section>
</article>
