<?php
/**
 * Functionality Index — all meaningful functionality grouped by purpose, with
 * filters. Filtering is server-side (a plain GET form) so it works without
 * JavaScript and produces deep-linkable URLs.
 *
 * @var Content $content
 */
$fStatus = isset($_GET['status']) ? (string) preg_replace('/[^a-z\-]/i', '', (string) $_GET['status']) : '';
$fArea = isset($_GET['area']) ? (string) preg_replace('/[^a-z\-]/i', '', (string) $_GET['area']) : '';
$fVer = isset($_GET['verification']) ? (string) preg_replace('/[^a-z\-]/i', '', (string) $_GET['verification']) : '';
$fFacing = isset($_GET['facing']) ? (string) preg_replace('/[^a-z]/i', '', (string) $_GET['facing']) : '';

$groups = $content->functionalityGroups();

$matches = function (array $m) use ($fStatus, $fArea, $fVer, $fFacing): bool {
    if ($fStatus !== '' && (string) ($m['status'] ?? '') !== $fStatus) {
        return false;
    }
    if ($fArea !== '' && (string) ($m['area'] ?? '') !== $fArea) {
        return false;
    }
    if ($fVer !== '' && (string) ($m['verification'] ?? '') !== $fVer) {
        return false;
    }
    if ($fFacing !== '') {
        $isFacing = (bool) ($m['user_facing'] ?? false);
        if ($fFacing === 'user' && !$isFacing) {
            return false;
        }
        if ($fFacing === 'system' && $isFacing) {
            return false;
        }
    }
    return true;
};

$anyFilter = ($fStatus !== '' || $fArea !== '' || $fVer !== '' || $fFacing !== '');
$shown = 0;
?>
<article class="view view-func-index">
    <header class="page-head reading-column">
        <p class="eyebrow">Functionality index</p>
        <h1>Everything the software does</h1>
        <p class="lede">Functionality is the primary unit. Each item is something the software does, with its real status and how it was verified.</p>
    </header>

    <form class="filters wide-section" method="get" action="<?= h(guide_url('functionality')) ?>">
        <input type="hidden" name="view" value="functionality">
        <div class="filters__row">
            <label>Status
                <select name="status">
                    <option value="">Any</option>
                    <?php foreach (status_vocabulary() as $k => $label): ?>
                        <option value="<?= h($k) ?>"<?= $fStatus === $k ? ' selected' : '' ?>><?= h($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Area
                <select name="area">
                    <option value="">Any</option>
                    <?php foreach ($groups as $g): ?>
                        <option value="<?= h($g['id']) ?>"<?= $fArea === $g['id'] ? ' selected' : '' ?>><?= h($g['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Verification
                <select name="verification">
                    <option value="">Any</option>
                    <?php foreach (verification_vocabulary() as $k => $label): ?>
                        <option value="<?= h($k) ?>"<?= $fVer === $k ? ' selected' : '' ?>><?= h($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Facing
                <select name="facing">
                    <option value="">Any</option>
                    <option value="user"<?= $fFacing === 'user' ? ' selected' : '' ?>>User-facing</option>
                    <option value="system"<?= $fFacing === 'system' ? ' selected' : '' ?>>System</option>
                </select>
            </label>
            <div class="filters__actions">
                <button type="submit" class="btn btn--primary">Filter</button>
                <a class="btn" id="clear-filters" href="<?= h(guide_url('functionality')) ?>">Clear</a>
            </div>
        </div>
    </form>

    <?php foreach ($groups as $group): ?>
        <?php $rows = array_filter($group['records'], fn ($r) => $matches($r['meta'])); ?>
        <?php if ($rows === []) { continue; } ?>
        <section class="group-block wide-section" aria-labelledby="grp-<?= h($group['id']) ?>">
            <header class="section-intro">
                <h2 id="grp-<?= h($group['id']) ?>"><?= h($group['title']) ?></h2>
                <?php if ($group['description'] !== ''): ?>
                    <p class="section-intro__support"><?= h($group['description']) ?></p>
                <?php endif; ?>
            </header>
            <ul class="record-list">
                <?php foreach ($rows as $rec): $m = $rec['meta']; $shown++; ?>
                    <li class="record-card"
                        data-status="<?= h((string) ($m['status'] ?? '')) ?>"
                        data-area="<?= h((string) ($m['area'] ?? '')) ?>"
                        data-verification="<?= h((string) ($m['verification'] ?? '')) ?>"
                        data-facing="<?= !empty($m['user_facing']) ? 'user' : 'system' ?>">
                        <div class="record-card__row">
                            <h3 class="record-card__title">
                                <a class="record-card__link" href="<?= h(functionality_url((string) $m['id'])) ?>">
                                    <?= h((string) ($m['title'] ?? $m['id'])) ?>
                                </a>
                            </h3>
                            <div class="record-card__status">
                                <?= status_badge((string) ($m['status'] ?? 'unknown')) ?>
                            </div>
                        </div>
                        <p class="record-card__summary"><?= h((string) ($m['summary'] ?? '')) ?></p>
                        <dl class="record-card__meta">
                            <?php if (($m['verification'] ?? '') !== ''): ?>
                                <div>
                                    <dt>Verification</dt>
                                    <dd><?= verification_badge((string) $m['verification']) ?></dd>
                                </div>
                            <?php endif; ?>
                            <?php if (($m['trigger'] ?? '') !== ''): ?>
                                <div>
                                    <dt>Trigger</dt>
                                    <dd><?= h((string) $m['trigger']) ?></dd>
                                </div>
                            <?php endif; ?>
                            <div>
                                <dt>Facing</dt>
                                <dd><?= !empty($m['user_facing']) ? 'User-facing' : 'System' ?></dd>
                            </div>
                            <div>
                                <dt>Updated</dt>
                                <dd><?= h((string) ($m['updated'] ?? 'unknown')) ?></dd>
                            </div>
                        </dl>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endforeach; ?>

    <p class="empty-state" id="filter-empty"<?= $shown === 0 ? '' : ' hidden' ?>>No functionality matches these filters. <a href="<?= h(guide_url('functionality')) ?>">Clear filters</a>.</p>
</article>
