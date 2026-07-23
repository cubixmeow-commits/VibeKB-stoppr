<?php
/**
 * Data & Storage — explains what the app stores and which functionality reads
 * and writes it, in terms of application behaviour (not a raw schema dump).
 *
 * @var Content $content
 */
$storage = $content->systemDoc('storage');
$dataFlow = $content->systemDoc('data-flow');

// Derive which functionality reads/writes each named store from the records.
$storeUse = [];
foreach ($content->allFunctionality() as $rid => $rec) {
    $m = $rec['meta'];
    foreach ((array) ($m['reads'] ?? []) as $store) {
        $storeUse[$store]['reads'][] = ['id' => $rid, 'title' => (string) ($m['title'] ?? $rid)];
    }
    foreach ((array) ($m['writes'] ?? []) as $store) {
        $storeUse[$store]['writes'][] = ['id' => $rid, 'title' => (string) ($m['title'] ?? $rid)];
    }
}
ksort($storeUse);
?>
<article class="view view-doc">
    <header class="page-head reading-column">
        <p class="eyebrow">Data &amp; storage</p>
        <h1>What the software stores</h1>
        <p class="lede">Where data comes from, where it goes, and what it means to the application.</p>
    </header>

    <?php if ($storage !== null): ?>
        <section class="doc-section content-section"><div class="prose reading-column"><?= $storage['html'] ?></div></section>
    <?php endif; ?>

    <?php if ($dataFlow !== null): ?>
        <section class="doc-section content-section">
            <header class="section-intro reading-column">
                <h2><?= h((string) ($dataFlow['meta']['title'] ?? 'How data flows')) ?></h2>
            </header>
            <div class="prose reading-column"><?= $dataFlow['html'] ?></div>
        </section>
    <?php endif; ?>

    <section class="doc-section content-section wide-section" aria-labelledby="data-use">
        <header class="section-intro reading-column">
            <h2 id="data-use">Which functionality touches each store</h2>
        </header>
        <?php if ($storeUse === []): ?>
            <p class="muted">No data stores are declared by functionality records.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <tr><th>Store</th><th>Written by</th><th>Read by</th></tr>
                    <?php foreach ($storeUse as $store => $use): ?>
                        <tr>
                            <td><code><?= h((string) $store) ?></code></td>
                            <td>
                                <?php foreach ($use['writes'] ?? [] as $f): ?>
                                    <a class="chip" href="<?= h(functionality_url($f['id'])) ?>"><?= h($f['title']) ?></a>
                                <?php endforeach; ?>
                                <?php if (empty($use['writes'])): ?><span class="muted">—</span><?php endif; ?>
                            </td>
                            <td>
                                <?php foreach ($use['reads'] ?? [] as $f): ?>
                                    <a class="chip" href="<?= h(functionality_url($f['id'])) ?>"><?= h($f['title']) ?></a>
                                <?php endforeach; ?>
                                <?php if (empty($use['reads'])): ?><span class="muted">—</span><?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        <?php endif; ?>
    </section>
</article>
