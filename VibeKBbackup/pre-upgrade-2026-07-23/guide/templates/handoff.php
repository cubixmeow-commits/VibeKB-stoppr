<?php
/**
 * AI Handoff — the concise current-state summary a new agent reads before
 * touching the project. Driven by work/handoff.md, with live links to the
 * open sessions.
 *
 * @var Content $content
 */
$handoff = $content->handoff();
$sessions = $content->sessions();
?>
<article class="view view-doc">
    <header class="page-head reading-column">
        <p class="eyebrow">AI handoff</p>
        <h1>Start here before you change anything</h1>
        <p class="lede">Everything the next human or AI session needs to avoid reconstructing the project incorrectly.</p>
    </header>

    <?php if ($handoff === null): ?>
        <p class="empty-state">No handoff recorded.</p>
    <?php else: ?>
        <section class="doc-section callout callout--work">
            <div class="badge-row">
                <?php if (($handoff['meta']['verification_state'] ?? '') !== ''): ?>
                    <?= badge('Verification: ' . str_replace('-', ' ', (string) $handoff['meta']['verification_state']), 'info') ?>
                <?php endif; ?>
                <span class="muted">Updated <?= h((string) ($handoff['meta']['updated'] ?? 'unknown')) ?></span>
            </div>
            <div class="prose reading-column"><?= $handoff['html'] ?></div>
        </section>
    <?php endif; ?>

    <?php if ($sessions !== []): ?>
        <section class="doc-section">
            <h2>Recent work sessions</h2>
            <ul class="why-list">
                <?php foreach ($sessions as $s): $m = $s['meta']; ?>
                    <li class="why-item">
                        <div class="why-item__head">
                            <h3><?= h((string) ($m['title'] ?? $m['id'] ?? 'Session')) ?></h3>
                            <div class="badge-row">
                                <?php if (($m['verification'] ?? '') !== ''): ?><?= verification_badge((string) $m['verification']) ?><?php endif; ?>
                                <span class="muted"><?= h((string) ($m['date'] ?? '')) ?></span>
                            </div>
                        </div>
                        <p><?= h((string) ($m['summary'] ?? '')) ?></p>
                        <?php if (!empty($m['functionality'])): ?>
                            <p class="why-item__links"><?= functionality_chips($content->resolveFunctionality($m['functionality'])) ?></p>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <nav class="cross-links" aria-label="Related views">
        <a class="btn" href="<?= h(guide_url('current-work')) ?>">Current AI work →</a>
        <a class="btn" href="<?= h(guide_url('overview')) ?>">Overview →</a>
    </nav>
</article>
