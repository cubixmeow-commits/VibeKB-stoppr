<?php
/**
 * Reference — the technical reference for the content model itself: record
 * types, controlled vocabularies, and live content validation diagnostics.
 *
 * @var Content $content
 * @var bool $devMode
 */
$issues = $content->issues();
$errors = array_values(array_filter($issues, fn ($i) => $i['level'] === 'error'));
$warnings = array_values(array_filter($issues, fn ($i) => $i['level'] === 'warn'));
$manifest = $content->manifest();
?>
<article class="view view-doc">
    <header class="page-head reading-column">
        <p class="eyebrow">Reference</p>
        <h1>Content model &amp; diagnostics</h1>
        <p class="lede">How this guide's content is structured, the vocabularies it validates against, and any problems detected in the current content set.</p>
    </header>

    <section class="doc-section" id="validation">
        <h2>Content validation</h2>
        <?php if ($issues === []): ?>
            <p class="callout callout--ok">No validation issues detected. Every record parses, every relationship resolves.</p>
        <?php else: ?>
            <p class="badge-row">
                <?= badge(count($errors) . ' error' . (count($errors) === 1 ? '' : 's'), count($errors) ? 'danger' : 'ok') ?>
                <?= badge(count($warnings) . ' warning' . (count($warnings) === 1 ? '' : 's'), count($warnings) ? 'warn' : 'ok') ?>
            </p>
            <?php if ($errors !== []): ?>
                <h3>Errors</h3>
                <ul class="issue-list issue-list--error">
                    <?php foreach ($errors as $i): ?><li><?= h($i['message']) ?></li><?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <?php if ($warnings !== []): ?>
                <h3>Warnings</h3>
                <ul class="issue-list issue-list--warn">
                    <?php foreach ($warnings as $i): ?><li><?= h($i['message']) ?></li><?php endforeach; ?>
                </ul>
            <?php endif; ?>
        <?php endif; ?>
    </section>

    <section class="doc-section">
        <h2>Record types</h2>
        <div class="table-wrap">
            <table>
                <tr><th>Type</th><th>Lives in</th><th>Purpose</th></tr>
                <tr><td>project</td><td><code>project/</code></td><td>Identity, intent, current state, constraints.</td></tr>
                <tr><td>functionality</td><td><code>functionality/records/</code></td><td>The primary unit — something the software does.</td></tr>
                <tr><td>system</td><td><code>system/</code></td><td>Mental model, components, flows, storage, deployment.</td></tr>
                <tr><td>file</td><td><code>files/important-files.json</code></td><td>Curated important files with safety levels.</td></tr>
                <tr><td>decision / constraint / assumption / warning / discovery / change</td><td><code>memory/</code></td><td>Repository memory that explains functionality.</td></tr>
                <tr><td>work / handoff / session</td><td><code>work/</code></td><td>Current AI work, handoff, and session history.</td></tr>
            </table>
        </div>
    </section>

    <section class="doc-section">
        <h2>Statuses</h2>
        <p class="badge-row"><?php foreach (status_vocabulary() as $k => $label): ?><?= status_badge($k) ?><?php endforeach; ?></p>
        <h2>Verification / provenance states</h2>
        <p class="badge-row"><?php foreach (array_keys(verification_vocabulary()) as $k): ?><?= verification_badge($k) ?><?php endforeach; ?></p>
        <h2>File safety levels</h2>
        <p class="badge-row"><?php foreach (safety_vocabulary() as $k => $label): ?><?= badge($label, safety_tone($k)) ?><?php endforeach; ?></p>
    </section>

    <section class="doc-section">
        <h2>Technical constraints</h2>
        <ul>
            <li>PHP 8.2, ordinary cPanel shared hosting.</li>
            <li>Deployable in a subfolder; query-string routing, no rewrite rules required.</li>
            <li>No database, no external API, no Node, no build step.</li>
            <li>All content is repository-owned files under <code>.vibekb/</code>.</li>
        </ul>
        <?php if (!empty($manifest['vibekb_version'])): ?>
            <p class="muted">Content model version <?= h((string) $manifest['vibekb_version']) ?>, updated <?= h((string) ($manifest['updated'] ?? 'unknown')) ?>.</p>
        <?php endif; ?>
    </section>
</article>
