<?php
/**
 * Not found — a restrained 404 that keeps the user oriented.
 *
 * @var string $missing
 */
?>
<article class="view view-doc">
    <header class="page-head">
        <p class="eyebrow">Not found</p>
        <h1>That page isn&#39;t here</h1>
        <p class="lede">The guide couldn&#39;t find what you asked for<?php if (!empty($missing)): ?> (<?= h($missing) ?>)<?php endif; ?>.</p>
    </header>
    <p class="button-row">
        <a class="btn btn--primary" href="<?= h(guide_url('overview')) ?>">Go to the overview</a>
        <a class="btn" href="<?= h(guide_url('functionality')) ?>">Browse functionality</a>
    </p>
</article>
