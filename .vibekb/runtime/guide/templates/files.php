<?php
/**
 * Files That Matter — a curated, explained list of important files. Not the
 * repository tree; only files worth understanding, each with a safety level.
 *
 * @var Content $content
 */
$files = $content->files();
?>
<article class="view view-files">
    <header class="page-head reading-column">
        <p class="eyebrow">Files that matter</p>
        <h1>The files worth understanding</h1>
        <p class="lede">A curated list — not the whole repository. Each file says what it does, when it runs, and how safe it is to change.</p>
    </header>

    <?php if ($files === []): ?>
        <p class="empty-state">No important files recorded.</p>
    <?php else: ?>
        <div class="file-list">
            <?php foreach ($files as $f): ?>
                <section class="file-card" id="<?= h((string) ($f['path'] ?? '')) ?>">
                    <div class="file-card__head">
                        <h2><code><?= h((string) ($f['path'] ?? 'unknown')) ?></code></h2>
                        <div class="file-card__badges">
                            <?= badge(safety_label((string) ($f['safety'] ?? 'unknown')), safety_tone((string) ($f['safety'] ?? ''))) ?>
                            <?php if (($f['provenance'] ?? '') !== ''): ?>
                                <?= verification_badge((string) $f['provenance']) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <p class="file-card__purpose"><?= h((string) ($f['purpose'] ?? '')) ?></p>
                    <dl class="file-card__meta">
                        <?php if (($f['runs_when'] ?? '') !== ''): ?>
                            <div><dt>Runs when</dt><dd><?= h((string) $f['runs_when']) ?></dd></div>
                        <?php endif; ?>
                        <?php if (!empty($f['functionality'])): ?>
                            <div><dt>Implements</dt><dd>
                                <?php foreach ((array) $f['functionality'] as $fid): $rec = $content->functionality((string) $fid); ?>
                                    <?php if ($rec !== null): ?>
                                        <a class="chip" href="<?= h(functionality_url((string) $fid)) ?>"><?= h((string) ($rec['meta']['title'] ?? $fid)) ?></a>
                                    <?php else: ?>
                                        <span class="chip chip--broken"><?= h((string) $fid) ?> ⚠</span>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </dd></div>
                        <?php endif; ?>
                        <?php if (!empty($f['depends_on'])): ?>
                            <div><dt>Depends on</dt><dd><?= file_chips($f['depends_on']) ?></dd></div>
                        <?php endif; ?>
                        <?php if (!empty($f['depended_on_by'])): ?>
                            <div><dt>Depended on by</dt><dd>
                                <?= h(implode('; ', array_map('strval', (array) $f['depended_on_by']))) ?>
                            </dd></div>
                        <?php endif; ?>
                        <?php if (($f['test_after_change'] ?? '') !== ''): ?>
                            <div><dt>Test after change</dt><dd><?= h((string) $f['test_after_change']) ?></dd></div>
                        <?php endif; ?>
                    </dl>
                </section>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</article>
