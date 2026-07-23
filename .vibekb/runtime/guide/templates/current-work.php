<?php
/**
 * Current AI Work — a concise, structured explanation of the active change:
 * what was asked, what the software does now, what it should do, impact, and
 * progress. Driven by work/current.md.
 *
 * @var Content $content
 */
$work = $content->currentWork();
?>
<article class="view view-doc">
    <header class="page-head reading-column">
        <p class="eyebrow">Current work</p>
        <h1>What AI is changing right now</h1>
        <p class="lede">The active development objective, its impact, and how far along it is — before, during, and after the change.</p>
    </header>

    <?php if ($work === null): ?>
        <p class="empty-state">No active AI work is recorded. The software model reflects the last completed change.</p>
    <?php else: $m = $work['meta']; ?>
        <section class="work-summary callout callout--work">
            <h2><?= h((string) ($m['title'] ?? 'Current work')) ?></h2>
            <div class="badge-row">
                <?= badge(str_replace('-', ' ', (string) ($m['status'] ?? 'unknown')), 'info') ?>
                <?php if (($m['verification_state'] ?? '') !== ''): ?>
                    <?= verification_badge((string) $m['verification_state']) ?>
                <?php endif; ?>
            </div>
            <dl class="rail-dl work-meta">
                <?php if (($m['objective'] ?? '') !== ''): ?><dt>Objective</dt><dd><?= h((string) $m['objective']) ?></dd><?php endif; ?>
                <?php if (!empty($m['affected_functionality'])): ?>
                    <dt>Affected functionality</dt><dd><?= functionality_chips($content->resolveFunctionality($m['affected_functionality'])) ?></dd>
                <?php endif; ?>
                <?php if (!empty($m['expected_files'])): ?>
                    <dt>Expected files</dt><dd><?= file_chips($m['expected_files']) ?></dd>
                <?php endif; ?>
                <?php if (($m['data_impact'] ?? '') !== ''): ?><dt>Data impact</dt><dd><?= h((string) $m['data_impact']) ?></dd><?php endif; ?>
                <?php if (!empty($m['risks'])): ?><dt>Risks</dt><dd><?= h(implode(' ', (array) $m['risks'])) ?></dd><?php endif; ?>
            </dl>
        </section>

        <section class="doc-section content-section">
            <div class="prose reading-column"><?= $work['html'] ?></div>
        </section>
    <?php endif; ?>

    <nav class="cross-links" aria-label="Related views">
        <a class="btn" href="<?= h(guide_url('handoff')) ?>">AI handoff →</a>
        <a class="btn" href="<?= h(guide_url('changes')) ?>">Completed changes →</a>
    </nav>
</article>
