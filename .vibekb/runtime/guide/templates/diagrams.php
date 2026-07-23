<?php
/**
 * Diagrams — source-grounded SVG maps that explain how the software works, for
 * readers who want a visual overview before diving into functionality records.
 *
 * Diagrams are first-class repository-owned records (`.vibekb/diagrams/`). Each
 * renders inline (accessible <title>/<desc> in the SVG), states its
 * verification and provenance, visibly labels inferred or unverified paths, and
 * links back to the functionality and warnings it relates to.
 *
 * @var Content $content
 */
$groups = $content->diagramGroups();
$all = $content->allDiagrams();
?>
<article class="view view-doc">
    <header class="page-head reading-column">
        <p class="eyebrow">Diagrams</p>
        <h1>Diagrams</h1>
        <p class="lede">Source-grounded visual maps of what the software is doing. Each diagram explains what you are seeing, how it was verified, and what remains uncertain — and links back to the functionality and warnings it relates to.</p>
        <p class="text-soft">Diagrams never claim behaviour that has not been traced to source. Inferred or unverified paths are labelled in the diagram itself.</p>
    </header>

    <?php if ($all === []): ?>
        <p class="empty-state">No diagrams recorded yet. Add source-grounded diagrams under <code>.vibekb/diagrams/</code>.</p>
    <?php else: ?>

    <nav class="diagram-toc wide-section" aria-label="Diagram index">
        <?php foreach ($groups as $group): ?>
            <?php foreach ($group['records'] as $rec): $m = $rec['meta']; ?>
                <a href="#<?= h((string) $m['id']) ?>">
                    <strong><?= h((string) ($m['title'] ?? $m['id'])) ?></strong>
                    <span class="text-soft"><?= h((string) ($m['summary'] ?? '')) ?></span>
                </a>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </nav>

    <?php foreach ($groups as $group): ?>
        <h2 class="diagram-group-title reading-column"><?= h($group['title']) ?></h2>
        <?php if ($group['description'] !== ''): ?>
            <p class="reading-column text-soft"><?= h($group['description']) ?></p>
        <?php endif; ?>

        <?php foreach ($group['records'] as $rec): $m = $rec['meta']; $id = (string) $m['id']; ?>
            <?php $topology = $content->resolvedTopology($id); ?>
            <section class="diagram-section wide-section<?= $topology !== null ? ' diagram-section--explainable' : '' ?>" id="<?= h($id) ?>" aria-labelledby="<?= h($id) ?>-h"<?= $topology !== null ? ' data-diagram="' . h($id) . '"' : '' ?>>
                <h3 id="<?= h($id) ?>-h"><?= h((string) ($m['title'] ?? $id)) ?></h3>
                <p><?= h((string) ($m['summary'] ?? '')) ?></p>

                <?php $svg = $content->diagramSvg((string) ($m['svg'] ?? '')); ?>
                <figure class="diagram-figure">
                    <div class="diagram-scroll">
                        <?php if ($svg !== null): ?>
                            <?= $svg /* trusted, repository-owned, validated as well-formed XML with title/desc */ ?>
                        <?php else: ?>
                            <p class="empty-state">Diagram asset <code><?= h((string) ($m['svg'] ?? '')) ?></code> could not be loaded.</p>
                        <?php endif; ?>
                    </div>
                    <figcaption class="diagram-caption"><strong>What am I looking at?</strong> <?= h((string) ($m['summary'] ?? '')) ?></figcaption>
                </figure>

                <?php if ($topology !== null): ?>
                    <?php require __DIR__ . '/partials/diagram-explain.php'; ?>
                <?php endif; ?>

                <dl class="diagram-meta reading-column">
                    <?php if (($m['diagram_type'] ?? '') !== ''): ?>
                        <dt>Diagram type</dt>
                        <dd><?= h(diagram_type_label((string) $m['diagram_type'])) ?></dd>
                    <?php endif; ?>
                    <dt>Verification</dt>
                    <dd><?= verification_badge((string) ($m['verification'] ?? 'not-verified')) ?></dd>
                    <?php if (($m['provenance'] ?? '') !== ''): ?>
                        <dt>Source evidence</dt>
                        <dd><?= h((string) $m['provenance']) ?></dd>
                    <?php endif; ?>
                    <?php if (($m['last_verified'] ?? '') !== ''): ?>
                        <dt>Last verified against source</dt>
                        <dd><?= h((string) $m['last_verified']) ?></dd>
                    <?php endif; ?>
                    <?php if (($m['uncertainty'] ?? '') !== ''): ?>
                        <dt>Uncertainty</dt>
                        <dd><?= h((string) $m['uncertainty']) ?></dd>
                    <?php endif; ?>
                </dl>

                <?php
                $relFn = $content->resolveFunctionality($m['functionality'] ?? []);
                $relWarn = $content->resolveMemory(array_map(fn ($w) => 'warning:' . $w, $content->asList($m['warnings'] ?? [])));
                $relDia = $content->resolveDiagrams($m['diagrams'] ?? []);
                ?>
                <?php if ($relFn !== [] || $relWarn !== [] || $relDia !== []): ?>
                    <div class="related-diagrams reading-column">
                        <?php if ($relFn !== []): ?>
                            <p><strong>Related functionality:</strong> <?= functionality_chips($relFn) ?></p>
                        <?php endif; ?>
                        <?php if ($relWarn !== []): ?>
                            <p><strong>Related warnings:</strong> <?= memory_chips($relWarn) ?></p>
                        <?php endif; ?>
                        <?php if ($relDia !== []): ?>
                            <p><strong>Related diagrams:</strong>
                            <?php foreach ($relDia as $d): ?>
                                <?php if ($d['resolved']): ?><a class="chip" href="<?= h(diagram_url($d['id'])) ?>"><?= h($d['title']) ?></a><?php else: ?><span class="chip chip--broken"><?= h($d['title']) ?> ⚠</span><?php endif; ?>
                            <?php endforeach; ?>
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <?php endif; ?>
</article>
