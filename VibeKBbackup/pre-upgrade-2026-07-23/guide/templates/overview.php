<?php
/**
 * Software Overview — the first screen. Builds a correct mental model of what
 * the software currently does before any browsing.
 *
 * @var Content $content
 * @var string $projectName
 */
$identity = $content->projectDoc('identity');
$currentState = $content->projectDoc('current-state');
$statusCounts = $content->statusCounts();
$groups = $content->functionalityGroups();
$warnings = $content->memoryByType('warnings');
$work = $content->currentWork();
$handoff = $content->handoff();
$mentalModel = $content->systemDoc('mental-model');
$storage = $content->systemDoc('storage');
$oneLiner = (string) ($identity['meta']['one_liner'] ?? $identity['meta']['summary'] ?? '');
$identitySummary = (string) ($identity['meta']['summary'] ?? '');
$currentSummary = (string) ($currentState['meta']['summary'] ?? '');
$total = array_sum($statusCounts);
$generation = $GLOBALS['vibekb_generation'] ?? ['mode' => 'dynamic'];
$provenance = provenance_data($content->manifest(), $generation);
$example = is_array($content->manifest()['example_project'] ?? null) ? $content->manifest()['example_project'] : [];
$exampleRepo = (string) ($example['source_repository'] ?? '');
$exampleName = (string) ($example['name'] ?? $projectName);
?>
<article class="view view-overview">

    <header class="page-head reading-column">
        <p class="eyebrow">Software overview</p>
        <h1><?= h($projectName) ?></h1>
        <?php if ($oneLiner !== ''): ?>
            <p class="lede"><?= h($oneLiner) ?></p>
        <?php endif; ?>
        <?php if (($identity['meta']['primary_outcome'] ?? '') !== ''): ?>
            <p class="sub"><strong>What it gives you:</strong> <?= h((string) $identity['meta']['primary_outcome']) ?></p>
        <?php endif; ?>

        <aside class="source-notice" aria-label="Example source notice">
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
    </header>

    <?php $areaCount = count($groups); ?>
    <section class="snapshot-bar wide-section" aria-label="At a glance">
        <div class="snapshot-bar__item">
            <p class="snapshot-bar__label">Tracked functionality</p>
            <p class="snapshot-bar__value"><?= (int) $total ?> record<?= $total === 1 ? '' : 's' ?></p>
            <p class="snapshot-bar__value snapshot-bar__value--quiet"><?= (int) $areaCount ?> functional area<?= $areaCount === 1 ? '' : 's' ?></p>
            <p class="badge-row"><?= status_count_badges($statusCounts) ?></p>
        </div>
        <div class="snapshot-bar__item">
            <p class="snapshot-bar__label">Storage</p>
            <p class="snapshot-bar__text"><?= h((string) ($storage['meta']['summary'] ?? 'See the data and storage view.')) ?></p>
            <p><a class="text-link" href="<?= h(guide_url('data')) ?>">Data &amp; storage →</a></p>
        </div>
        <div class="snapshot-bar__item">
            <p class="snapshot-bar__label">Source commit analyzed</p>
            <p class="snapshot-bar__value snapshot-bar__value--quiet"><?= h((string) ($provenance['source_commit'] ?? 'unknown')) ?></p>
            <?php if (($provenance['last_verified'] ?? '') !== ''): ?>
                <p class="snapshot-bar__text">Last verified against source: <?= h((string) $provenance['last_verified']) ?></p>
            <?php endif; ?>
        </div>
    </section>

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

    <section class="content-section wide-section" aria-labelledby="ov-areas">
        <header class="section-intro reading-column">
            <h2 id="ov-areas">Functional areas</h2>
            <p class="section-intro__support"><?= h(count_records_phrase($total, count($groups))) ?>. Open any area for its records, or browse the full index.</p>
        </header>
        <ul class="area-summary-list">
            <?php foreach ($groups as $group): ?>
                <?php
                $count = count($group['records']);
                $groupStatuses = [];
                foreach ($group['records'] as $rec) {
                    $st = (string) ($rec['meta']['status'] ?? 'unknown');
                    $groupStatuses[$st] = ($groupStatuses[$st] ?? 0) + 1;
                }
                $samples = array_slice($group['records'], 0, 2);
                $areaUrl = guide_url('functionality', ['area' => $group['id']]);
                ?>
                <li class="area-summary-card">
                    <div class="area-summary-card__head">
                        <h3 class="area-summary-card__title">
                            <a href="<?= h($areaUrl) ?>"><?= h($group['title']) ?></a>
                        </h3>
                        <p class="area-summary-card__count"><?= (int) $count ?> record<?= $count === 1 ? '' : 's' ?></p>
                    </div>
                    <?php if ($group['description'] !== ''): ?>
                        <p class="area-summary-card__desc"><?= h($group['description']) ?></p>
                    <?php endif; ?>
                    <p class="badge-row badge-row--quiet">
                        <?php foreach (status_vocabulary() as $key => $label): ?>
                            <?php if (!empty($groupStatuses[$key])): ?>
                                <?= badge($label . ' · ' . $groupStatuses[$key], status_tone($key)) ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </p>
                    <?php if ($samples !== []): ?>
                        <ul class="area-summary-card__samples">
                            <?php foreach ($samples as $rec): $m = $rec['meta']; ?>
                                <li>
                                    <a href="<?= h(functionality_url((string) $m['id'])) ?>"><?= h((string) ($m['title'] ?? $m['id'])) ?></a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <p class="area-summary-card__action">
                        <a class="text-link" href="<?= h($areaUrl) ?>">View this area →</a>
                    </p>
                </li>
            <?php endforeach; ?>
        </ul>
        <p class="wide-section__footer"><a class="text-link" href="<?= h(guide_url('functionality')) ?>">Open full functionality index →</a></p>
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
