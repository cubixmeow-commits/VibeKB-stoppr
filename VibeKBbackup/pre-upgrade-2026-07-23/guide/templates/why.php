<?php
/**
 * Why It Works This Way — supporting repository memory, kept connected to
 * functionality. Renders either the full index of memory records grouped by
 * type, or a single record when ?type=&id= are present.
 *
 * @var Content $content
 */
$reqType = isset($_GET['type']) ? (string) preg_replace('/[^a-z]/i', '', (string) $_GET['type']) : '';
$reqId = isset($_GET['id']) ? (string) preg_replace('/[^a-z0-9\-]/i', '', (string) $_GET['id']) : '';

$typeLabels = [
    'decisions' => 'Decisions',
    'constraints' => 'Constraints',
    'assumptions' => 'Assumptions',
    'warnings' => 'Warnings',
    'discoveries' => 'Discoveries',
    'changes' => 'Changes',
];

if ($reqType !== '' && $reqId !== '') {
    // Single memory record.
    $rec = $content->memoryRecord($reqType, $reqId);
    if ($rec === null) {
        http_response_code(404);
        echo '<article class="view"><p class="breadcrumb"><a href="' . h(guide_url('why')) . '">← Decisions &amp; rationale</a></p>';
        echo '<h1>Record not found</h1><p class="muted">No ' . h(rtrim($reqType, 's')) . ' with id <code>' . h($reqId) . '</code>.</p></article>';
        return;
    }
    $m = $rec['meta'];
    ?>
    <article class="view view-doc">
        <p class="breadcrumb"><a href="<?= h(guide_url('why')) ?>">← Decisions &amp; rationale</a></p>
        <header class="page-head reading-column">
            <p class="eyebrow"><?= h($typeLabels[$reqType] ?? ucfirst($reqType)) ?></p>
            <h1><?= h((string) ($m['title'] ?? $reqId)) ?></h1>
            <p class="lede"><?= h((string) ($m['summary'] ?? '')) ?></p>
            <div class="badge-row">
                <?php if (($m['status'] ?? '') !== ''): ?><?= badge((string) $m['status'], 'info') ?><?php endif; ?>
                <?php if (($m['severity'] ?? '') !== ''): ?><?= badge('Severity: ' . (string) $m['severity'], severity_tone((string) $m['severity'])) ?><?php endif; ?>
                <?php if (($m['confidence'] ?? '') !== ''): ?><?= badge('Confidence: ' . (string) $m['confidence'], 'muted') ?><?php endif; ?>
                <?php if (($m['verification'] ?? '') !== ''): ?><?= verification_badge((string) $m['verification']) ?><?php endif; ?>
            </div>
        </header>
        <div class="detail-grid">
            <div class="detail-main"><div class="prose reading-column"><?= $rec['html'] ?></div></div>
            <aside class="detail-rail" aria-label="Connections">
                <div class="rail-card">
                    <h2>Affects functionality</h2>
                    <p><?= functionality_chips($content->resolveFunctionality($m['functionality'] ?? [])) ?></p>
                </div>
                <?php if (!empty($m['files'])): ?>
                    <div class="rail-card"><h2>Files</h2><p><?= file_chips($m['files']) ?></p></div>
                <?php endif; ?>
            </aside>
        </div>
    </article>
    <?php
    return;
}

// Index of all memory, grouped by type.
$memory = $content->memory();
?>
<article class="view view-why">
    <header class="page-head reading-column">
        <p class="eyebrow">Decisions &amp; rationale</p>
        <h1>The reasoning behind the software</h1>
        <p class="lede">Decisions, constraints, assumptions, warnings, and discoveries — each connected to the functionality it explains. Repository memory supports the software model; it does not replace it.</p>
    </header>

    <?php foreach ($typeLabels as $type => $label): ?>
        <?php $records = $memory[$type] ?? []; if ($records === []) { continue; } ?>
        <section class="why-group" aria-labelledby="why-<?= h($type) ?>">
            <h2 id="why-<?= h($type) ?>"><?= h($label) ?></h2>
            <ul class="why-list">
                <?php foreach ($records as $rid => $rec): $m = $rec['meta']; ?>
                    <li class="why-item">
                        <div class="why-item__head">
                            <h3><a href="<?= h(memory_url($type, (string) $rid)) ?>"><?= h((string) ($m['title'] ?? $rid)) ?></a></h3>
                            <div class="badge-row">
                                <?php if (($m['severity'] ?? '') !== ''): ?><?= badge((string) $m['severity'], severity_tone((string) $m['severity'])) ?><?php endif; ?>
                                <?php if (($m['verification'] ?? '') !== ''): ?><?= verification_badge((string) $m['verification']) ?><?php endif; ?>
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
    <?php endforeach; ?>
</article>
