<?php
/**
 * Shared page shell: brand bar, sidebar navigation, body template, footer.
 *
 * @var Content $content
 * @var string $projectName
 * @var string $view
 * @var string $pageTitle
 * @var string $bodyTemplate
 * @var array<int, array{view: string, label: string}> $navItems
 * @var array<int, array{view: string, label: string}> $navPrimary
 * @var array<int, array{view: string, label: string}> $navSecondary
 * @var bool $devMode
 */
$issues = $content->issues();
$errorCount = count(array_filter($issues, fn ($i) => $i['level'] === 'error'));
$navPrimary = $navPrimary ?? $navItems;
$navSecondary = $navSecondary ?? [];
$generation = $GLOBALS['vibekb_generation'] ?? ['mode' => 'dynamic'];
$provenance = provenance_data($content->manifest(), $generation);

$isActive = static function (string $itemView, string $currentView): bool {
    return ($itemView === $currentView)
        || ($currentView === 'functionality' && $itemView === 'functionality');
};
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($pageTitle) ?> · <?= h($projectName) ?> — VibeKB</title>
    <meta name="description" content="A living explanation of what <?= h($projectName) ?> currently does, how it works, what AI is changing, and why.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- Web fonts are a progressive enhancement only. The stylesheet ships a
         full system-font fallback stack, so the guide renders correctly with no
         external CDN — required for offline and locked-down static hosting. -->
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= h(guide_asset('assets/css/guide.css')) ?>">
</head>
<body data-mode="<?= h((string) ($generation['mode'] ?? 'dynamic')) ?>">
<a class="skip-link" href="#main">Skip to content</a>

<div class="app-shell">
    <header class="site-header">
        <div class="site-header__inner">
            <a class="brand" href="<?= h(guide_url('overview')) ?>">
                <span class="brand__mark">VibeKB</span>
                <span class="brand__project"><?= h($projectName) ?></span>
            </a>
            <form class="site-search" role="search" action="<?= h(guide_url('search')) ?>">
                <label class="visually-hidden" for="site-search-input">Search guide</label>
                <input id="site-search-input" name="q" type="search" placeholder="Search guide…" autocomplete="off">
            </form>
            <button
                class="nav-toggle"
                type="button"
                aria-expanded="false"
                aria-controls="guide-sidebar"
                hidden
            >Menu</button>
        </div>
    </header>

    <div class="nav-backdrop" id="nav-backdrop" hidden></div>

    <aside class="sidebar" id="guide-sidebar" aria-label="Guide navigation">
        <nav class="sidebar-nav" aria-label="Guide sections">
            <p class="sidebar-nav__label" id="nav-primary-label">Primary</p>
            <ul class="sidebar-nav__list" aria-labelledby="nav-primary-label">
                <?php foreach ($navPrimary as $item): ?>
                    <?php $active = $isActive($item['view'], $view); ?>
                    <li>
                        <a href="<?= h(guide_url($item['view'])) ?>"<?= $active ? ' aria-current="page"' : '' ?>>
                            <?= $item['label'] ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php if ($navSecondary !== []): ?>
                <p class="sidebar-nav__label" id="nav-explore-label">Explore</p>
                <ul class="sidebar-nav__list sidebar-nav__list--secondary" aria-labelledby="nav-explore-label">
                    <?php foreach ($navSecondary as $item): ?>
                        <?php $active = $isActive($item['view'], $view); ?>
                        <li>
                            <a href="<?= h(guide_url($item['view'])) ?>"<?= $active ? ' aria-current="page"' : '' ?>>
                                <?= $item['label'] ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </nav>
    </aside>

    <div class="app-main">
        <?php if (($generation['mode'] ?? 'dynamic') === 'static'): ?>
            <p class="generated-notice" role="note">
                Generated output — this is a static snapshot rendered from <code>.vibekb/</code>. The
                <code>.vibekb/</code> content is the source of truth. See <a href="<?= h(guide_url('reference')) ?>">Reference</a> for provenance.
            </p>
        <?php endif; ?>
        <?php if ($devMode && $errorCount > 0): ?>
            <div class="wrap">
                <p class="content-alert" role="status">
                    <strong><?= (int) $errorCount ?> content validation error<?= $errorCount === 1 ? '' : 's' ?></strong>
                    — see the <a href="<?= h(guide_url('reference')) ?>#validation">Reference</a> view. (Shown in development only.)
                </p>
            </div>
        <?php endif; ?>

        <main id="main" class="wrap">
            <?php
            $bodyFile = __DIR__ . '/' . $bodyTemplate . '.php';
            if (is_file($bodyFile)) {
                require $bodyFile;
            } else {
                echo '<p>View unavailable.</p>';
            }
            ?>
        </main>

        <footer class="site-footer">
            <div class="wrap site-footer__inner">
                <p><strong>VibeKB</strong> — Understand what your software is doing.</p>
                <p class="muted">
                    A living explanation generated from repository-owned content in <code>.vibekb/</code>.
                    <a href="<?= h(site_root_url()) ?>">About VibeKB</a>
                </p>
                <p class="muted site-footer__provenance"><?= provenance_stamp($provenance) ?></p>
            </div>
        </footer>
    </div>
</div>

<script src="<?= h(guide_asset('assets/js/guide.js')) ?>" defer></script>
</body>
</html>
