<?php
/**
 * Functionality Detail — the most important view. The narrative sections (A–Q)
 * come from the record's Markdown body; the structured header and the
 * relationship rails are driven by front matter and resolved against other
 * records so every link is live and validated.
 *
 * @var Content $content
 * @var array<string, mixed> $record
 * @var string $id
 */
$m = $record['meta'];
$deps = $content->resolveFunctionality($m['depends_on'] ?? []);
$dependents = $content->dependentsOf($id);
$mem = $content->resolveMemory($m['related_memory'] ?? []);
$fileRecords = $content->filesForFunctionality($id);
$relatedDiagrams = $content->diagramsForFunctionality($id);
$diagramNodes = $content->diagramNodesForFunctionality($id);
$reads = is_array($m['reads'] ?? null) ? $m['reads'] : [];
$writes = is_array($m['writes'] ?? null) ? $m['writes'] : [];
$config = $m['config'] ?? [];
$hasConfig = ($config !== '' && $config !== [] && $config !== null);
$primaryFiles = $m['files'] ?? [];
$longDeps = count($deps) > 6;
$longDependents = count($dependents) > 6;
$longMem = count($mem) > 6;
$longFiles = count($fileRecords) > 8 || (is_array($primaryFiles) && count($primaryFiles) > 8);
?>
<article class="view view-func-detail">
    <p class="breadcrumb"><a href="<?= h(guide_url('functionality')) ?>">← All functionality</a></p>

    <header class="page-head reading-column">
        <p class="eyebrow"><?= h(ucfirst(str_replace('-', ' ', (string) ($m['area'] ?? 'functionality')))) ?></p>
        <h1><?= h((string) ($m['title'] ?? $id)) ?></h1>
        <p class="lede"><?= h((string) ($m['summary'] ?? '')) ?></p>
        <div class="badge-row">
            <?= status_badge((string) ($m['status'] ?? 'unknown')) ?>
            <?php if (($m['verification'] ?? '') !== ''): ?><?= verification_badge((string) $m['verification']) ?><?php endif; ?>
            <?= badge(!empty($m['user_facing']) ? 'User-facing' : 'System', !empty($m['user_facing']) ? 'info' : 'muted') ?>
        </div>
    </header>

    <div class="detail-grid">
        <div class="detail-main">
            <?php if (($record['html'] ?? '') !== ''): ?>
                <div class="prose reading-column"><?= $record['html'] ?></div>
            <?php else: ?>
                <p class="muted">No narrative recorded for this functionality yet.</p>
            <?php endif; ?>
        </div>

        <aside class="detail-rail" aria-label="Record metadata">
            <div class="rail-card metadata-group">
                <h2>Status</h2>
                <dl class="rail-dl">
                    <dt>Status</dt>
                    <dd><?= status_badge((string) ($m['status'] ?? 'unknown')) ?></dd>
                    <?php if (($m['verification'] ?? '') !== ''): ?>
                        <dt>Verification</dt>
                        <dd><?= verification_badge((string) $m['verification']) ?></dd>
                    <?php endif; ?>
                    <dt>Updated</dt>
                    <dd><?= h((string) ($m['updated'] ?? 'unknown')) ?></dd>
                    <dt>Facing</dt>
                    <dd><?= !empty($m['user_facing']) ? 'User-facing' : 'System' ?></dd>
                </dl>
            </div>

            <?php if (($m['trigger'] ?? '') !== ''): ?>
                <div class="rail-card metadata-group">
                    <h2>Trigger</h2>
                    <p><?= h((string) $m['trigger']) ?></p>
                </div>
            <?php endif; ?>

            <div class="rail-card metadata-group">
                <h2>Data read or written</h2>
                <p><strong>Reads:</strong> <?= file_chips($reads) ?></p>
                <p><strong>Writes:</strong> <?= file_chips($writes) ?></p>
                <?php if ($hasConfig): ?>
                    <p><strong>Config:</strong> <?= file_chips($config) ?></p>
                <?php endif; ?>
            </div>

            <div class="rail-card metadata-group">
                <h2>Files involved</h2>
                <?php if ($fileRecords !== []): ?>
                    <?php
                    $fileItems = $fileRecords;
                    $shownFiles = $longFiles ? array_slice($fileItems, 0, 5) : $fileItems;
                    $hiddenFiles = $longFiles ? array_slice($fileItems, 5) : [];
                    ?>
                    <ul class="rail-list">
                        <?php foreach ($shownFiles as $f): ?>
                            <li>
                                <a href="<?= h(guide_url('files')) ?>#<?= h(rawurlencode((string) $f['path'])) ?>"><code><?= h((string) $f['path']) ?></code></a>
                                <?= badge(safety_label((string) ($f['safety'] ?? 'unknown')), safety_tone((string) ($f['safety'] ?? ''))) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php if ($hiddenFiles !== []): ?>
                        <details class="rail-disclose">
                            <summary>Show <?= count($hiddenFiles) ?> more file<?= count($hiddenFiles) === 1 ? '' : 's' ?></summary>
                            <ul class="rail-list">
                                <?php foreach ($hiddenFiles as $f): ?>
                                    <li>
                                        <a href="<?= h(guide_url('files')) ?>#<?= h(rawurlencode((string) $f['path'])) ?>"><code><?= h((string) $f['path']) ?></code></a>
                                        <?= badge(safety_label((string) ($f['safety'] ?? 'unknown')), safety_tone((string) ($f['safety'] ?? ''))) ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </details>
                    <?php endif; ?>
                <?php else: ?>
                    <p><?= file_chips($primaryFiles) ?></p>
                <?php endif; ?>
            </div>

            <div class="rail-card metadata-group">
                <h2>Dependencies</h2>
                <?php if ($longDeps): ?>
                    <details class="rail-disclose" open>
                        <summary>Depends on (<?= count($deps) ?>)</summary>
                        <p><?= functionality_chips($deps) ?></p>
                    </details>
                <?php else: ?>
                    <p><strong>Depends on:</strong> <?= functionality_chips($deps) ?></p>
                <?php endif; ?>
                <?php if ($longDependents): ?>
                    <details class="rail-disclose">
                        <summary>Depended on by (<?= count($dependents) ?>)</summary>
                        <p><?= functionality_chips($dependents) ?></p>
                    </details>
                <?php else: ?>
                    <p><strong>Depended on by:</strong> <?= functionality_chips($dependents) ?></p>
                <?php endif; ?>
            </div>

            <?php if ($relatedDiagrams !== [] || $diagramNodes !== []): ?>
                <div class="rail-card metadata-group">
                    <h2>Related diagrams</h2>
                    <?php if ($relatedDiagrams !== []): ?>
                        <p>
                            <?php foreach ($relatedDiagrams as $d): ?>
                                <a class="chip" href="<?= h(diagram_url($d['id'])) ?>"><?= h($d['title']) ?></a>
                            <?php endforeach; ?>
                        </p>
                    <?php endif; ?>
                    <?php if ($diagramNodes !== []): ?>
                        <p class="rail-note text-soft">Appears as a node in:</p>
                        <ul class="rail-list">
                            <?php foreach ($diagramNodes as $n): ?>
                                <li><a href="<?= h(diagram_anchor_url('node-' . $n['node'])) ?>"><?= h($n['node_title']) ?></a> <span class="text-soft">· <?= h($n['diagram_title']) ?></span></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="rail-card metadata-group">
                <h2>Related rationale</h2>
                <?php if ($longMem): ?>
                    <details class="rail-disclose">
                        <summary>Memory links (<?= count($mem) ?>)</summary>
                        <p><?= memory_chips($mem) ?></p>
                    </details>
                <?php else: ?>
                    <p><?= memory_chips($mem) ?></p>
                <?php endif; ?>
            </div>
        </aside>
    </div>
</article>
