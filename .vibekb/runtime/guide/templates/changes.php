<?php
/**
 * Changes — meaningful behavioural changes (not every edit). Each explains
 * before/after and links to the functionality it affected.
 *
 * @var Content $content
 */
$changes = $content->changes();
uasort($changes, fn ($a, $b) => strcmp((string) ($b['meta']['updated'] ?? ''), (string) ($a['meta']['updated'] ?? '')));
?>
<article class="view view-changes">
    <header class="page-head reading-column">
        <p class="eyebrow">Changes</p>
        <h1>What changed, and why it matters</h1>
        <p class="lede">Meaningful shifts in what the software does — not a log of every edit.</p>
    </header>

    <?php if ($changes === []): ?>
        <p class="empty-state">No changes recorded yet.</p>
    <?php else: ?>
        <ol class="change-list">
            <?php foreach ($changes as $cid => $c): $m = $c['meta']; ?>
                <li class="change-card">
                    <div class="change-card__head">
                        <h2><?= h((string) ($m['title'] ?? $cid)) ?></h2>
                        <div class="badge-row">
                            <?php if (($m['status'] ?? '') !== ''): ?><?= badge((string) $m['status'], 'info') ?><?php endif; ?>
                            <?php if (($m['verification'] ?? '') !== ''): ?><?= verification_badge((string) $m['verification']) ?><?php endif; ?>
                            <span class="muted"><?= h((string) ($m['updated'] ?? '')) ?></span>
                        </div>
                    </div>
                    <p><?= h((string) ($m['summary'] ?? '')) ?></p>
                    <div class="prose reading-column change-card__body"><?= $c['html'] ?></div>
                    <?php if (!empty($m['functionality'])): ?>
                        <p class="change-card__links"><strong>Affected:</strong>
                            <?= functionality_chips($content->resolveFunctionality($m['functionality'])) ?>
                        </p>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>
</article>
