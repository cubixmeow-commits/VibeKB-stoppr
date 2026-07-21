<?php
/**
 * How It Works — a system-level explanation assembled from the system/ docs.
 *
 * @var Content $content
 */
$order = [
    'mental-model' => 'The simplest mental model',
    'components' => 'Major components',
    'request-flow' => 'The request lifecycle',
    'deployment' => 'Deployment',
];
?>
<article class="view view-doc">
    <header class="page-head reading-column">
        <p class="eyebrow">Architecture</p>
        <h1>How the software works</h1>
        <p class="lede">A paced, system-level explanation — the mental model first, then the parts, the request lifecycle, and how it ships.</p>
    </header>

    <?php foreach ($order as $name => $fallback): ?>
        <?php $doc = $content->systemDoc($name); if ($doc === null) { continue; } ?>
        <section class="doc-section content-section" aria-labelledby="hiw-<?= h($name) ?>">
            <header class="section-intro reading-column">
                <h2 id="hiw-<?= h($name) ?>"><?= h((string) ($doc['meta']['title'] ?? $fallback)) ?></h2>
            </header>
            <div class="prose reading-column"><?= $doc['html'] ?></div>
        </section>
    <?php endforeach; ?>

    <nav class="cross-links" aria-label="Related views">
        <a class="btn" href="<?= h(guide_url('data')) ?>">Data &amp; storage →</a>
        <a class="btn" href="<?= h(guide_url('files')) ?>">Files that matter →</a>
    </nav>
</article>
