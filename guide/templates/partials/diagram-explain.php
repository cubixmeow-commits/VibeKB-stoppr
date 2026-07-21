<?php
/**
 * Explainable-diagram explanation block.
 *
 * The semantic, no-JavaScript core of the Explainable Diagrams feature. Every
 * node and edge in the diagram's topology is rendered as a complete, anchored
 * explanation section (`#node-<id>` / `#edge-<id>`) that the SVG markers point
 * at. A reader with no JavaScript can follow a marker to its section and read
 * everything; the small enhancement in guide.js only adds selection/dimming.
 *
 * Nodes answer "what is this?"; edges answer "why are these connected?"; files
 * answer "where is this implemented, and why should I read it?"; folders answer
 * "where does it live?"; source links answer "show me the implementation."
 *
 * @var Content $content
 * @var string $id       the diagram id
 * @var array{version:int, nodes: array<string,array<string,mixed>>, edges: list<array<string,mixed>>} $topology
 */
?>
<div class="diagram-explain" id="<?= h($id) ?>-explain">
    <div class="dx-legend" role="note">
        <span class="dx-legend__title">How to read this</span>
        <span class="dx-legend__item"><span class="dx-swatch dx-swatch--solid" aria-hidden="true"></span> Solid line &mdash; verified from source</span>
        <span class="dx-legend__item"><span class="dx-swatch dx-swatch--dashed" aria-hidden="true"></span> Dashed line &mdash; inferred from source</span>
        <span class="dx-legend__hint text-soft">Select any box or arrow in the diagram to jump to its explanation.</span>
    </div>

    <h4 class="dx-heading" id="<?= h($id) ?>-nodes-h">Parts &mdash; what each one is</h4>
    <p class="dx-heading-note text-soft">Each part explains why it appears, where it lives in the repository, and which files to read.</p>
    <div class="dx-list" role="list">
        <?php foreach ($topology['nodes'] as $nid => $node): ?>
            <section class="dx-card dx-node" role="listitem" id="node-<?= h($nid) ?>" data-node="<?= h($nid) ?>" tabindex="-1" aria-labelledby="node-<?= h($nid) ?>-h">
                <div class="dx-card__head">
                    <h5 class="dx-card__title" id="node-<?= h($nid) ?>-h"><?= h((string) $node['title']) ?></h5>
                    <?php if ((string) $node['verification'] !== ''): ?>
                        <?= verification_badge((string) $node['verification']) ?>
                    <?php endif; ?>
                </div>
                <p class="dx-card__purpose"><?= h((string) $node['purpose']) ?></p>

                <?php if ($node['functionality'] !== []): ?>
                    <p class="dx-rel"><strong>Related functionality:</strong> <?= functionality_chips($node['functionality']) ?></p>
                <?php endif; ?>

                <?php
                $nodeFilePaths = array_map(fn ($f) => (string) $f['path'], $node['files']);
                $tree = diagram_location_tree($nodeFilePaths, (string) $node['location']);
                ?>
                <?php if ((string) $node['location'] !== '' || $tree !== ''): ?>
                    <div class="dx-where">
                        <span class="dx-where__label">Where it lives</span>
                        <?php if ((string) $node['location'] !== ''): ?>
                            <code class="dx-where__path"><?= h((string) $node['location']) ?>/</code>
                        <?php else: ?>
                            <span class="text-soft">External to this repository.</span>
                        <?php endif; ?>
                        <?php if ($tree !== ''): ?><?= $tree ?><?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if ($node['files'] !== []): ?>
                    <div class="dx-files">
                        <span class="dx-files__label">Files that implement or support it</span>
                        <ul class="dx-files__list">
                            <?php foreach ($node['files'] as $file): ?>
                                <?= diagram_file_item($file) ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ($node['warnings'] !== []): ?>
                    <p class="dx-warn"><strong>&#9888; Warnings:</strong> <?= memory_chips($node['warnings']) ?></p>
                <?php endif; ?>
                <?php if ((string) $node['uncertainty'] !== ''): ?>
                    <p class="dx-uncertain"><strong>Uncertain:</strong> <?= h((string) $node['uncertainty']) ?></p>
                <?php endif; ?>
            </section>
        <?php endforeach; ?>
    </div>

    <h4 class="dx-heading" id="<?= h($id) ?>-edges-h">Connections &mdash; why they connect</h4>
    <p class="dx-heading-note text-soft">Each connection names the concrete mechanism linking its two parts. The mechanism is the proof the relationship passed the explainability gate.</p>
    <div class="dx-list" role="list">
        <?php foreach ($topology['edges'] as $edge): $eid = (string) $edge['id']; ?>
            <section class="dx-card dx-edge<?= $edge['verified'] ? ' dx-edge--verified' : ' dx-edge--inferred' ?>" role="listitem" id="edge-<?= h($eid) ?>" data-edge="<?= h($eid) ?>" tabindex="-1" aria-labelledby="edge-<?= h($eid) ?>-h">
                <div class="dx-card__head">
                    <h5 class="dx-card__title dx-edge__title" id="edge-<?= h($eid) ?>-h">
                        <a class="dx-edge__endpoint" href="#node-<?= h((string) $edge['from']) ?>"><?= h((string) $edge['from_title']) ?></a>
                        <span class="dx-mech"><?= h(edge_mechanism_label((string) $edge['mechanism'])) ?> <span aria-hidden="true">&rarr;</span></span>
                        <a class="dx-edge__endpoint" href="#node-<?= h((string) $edge['to']) ?>"><?= h((string) $edge['to_title']) ?></a>
                    </h5>
                    <span class="dx-verif"><?= edge_verification_badge((string) $edge['verification']) ?></span>
                </div>
                <p class="dx-card__purpose"><?= h((string) $edge['explanation']) ?></p>

                <?php if ((string) $edge['basis'] !== ''): ?>
                    <p class="dx-basis"><strong>Basis for the inference:</strong> <?= h((string) $edge['basis']) ?></p>
                <?php endif; ?>

                <?php if ($edge['functionality'] !== []): ?>
                    <p class="dx-rel"><strong>Related functionality:</strong> <?= functionality_chips($edge['functionality']) ?></p>
                <?php endif; ?>

                <?php if ($edge['files'] !== []): ?>
                    <div class="dx-files">
                        <span class="dx-files__label">Files behind this connection</span>
                        <ul class="dx-files__list">
                            <?php foreach ($edge['files'] as $file): ?>
                                <?= diagram_file_item($file) ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ($edge['warnings'] !== []): ?>
                    <p class="dx-warn"><strong>&#9888; Warnings:</strong> <?= memory_chips($edge['warnings']) ?></p>
                <?php endif; ?>
                <?php if ((string) $edge['uncertainty'] !== ''): ?>
                    <p class="dx-uncertain"><strong>Uncertain:</strong> <?= h((string) $edge['uncertainty']) ?></p>
                <?php endif; ?>
            </section>
        <?php endforeach; ?>
    </div>
</div>
