<?php

declare(strict_types=1);

/**
 * Provenance: the objective, honest statement of where a VibeKB guide's
 * understanding comes from and how fresh it is.
 *
 * VibeKB explains a *source* application (for the canonical example, SousMeow)
 * by reading it read-only. The guide is a rendering of that analysis. Two
 * distinct facts must never be conflated:
 *
 *   1. The **source** being explained — its repository, branch, the commit that
 *      was actually analysed, the verification scope, and when it was last
 *      re-verified. This lives in `manifest.json` (`provenance` / legacy
 *      `example_project`).
 *   2. The **generation** event that produced *this* output — the mode
 *      (dynamic live render vs static snapshot), when it was generated, and
 *      from which VibeKB commit. This is supplied at render/generation time.
 *
 * Neither fact implies the guide updates itself. A static snapshot reflects the
 * source commit at generation time; later source changes are not reflected
 * until VibeKB is updated and regenerated. This module deliberately produces
 * objective labels (Source commit analyzed, Analysis generated, Work-record
 * status, Last verified against source) and never undefined ones such as
 * "Last meaningful update" or "Current AI work: completed".
 */

/**
 * Normalise provenance for rendering from the manifest plus a generation
 * context. Missing values become empty strings so templates can decide what to
 * show; nothing is invented.
 *
 * @param array<string, mixed> $manifest    Parsed `.vibekb/manifest.json`.
 * @param array<string, mixed> $generation  Render/generation context. Keys:
 *        `mode` ("dynamic"|"static"), `generated` (ISO date/time),
 *        `generator_repository`, `generator_commit`, `generator_branch`.
 * @return array<string, string|bool>
 */
function provenance_data(array $manifest, array $generation = []): array
{
    // Prefer an explicit `provenance` block; fall back to the legacy
    // `example_project` block so existing manifests keep working.
    $src = [];
    if (is_array($manifest['provenance'] ?? null)) {
        $src = $manifest['provenance'];
    } elseif (is_array($manifest['example_project'] ?? null)) {
        $ex = $manifest['example_project'];
        $src = [
            'source_repository' => $ex['source_repository'] ?? '',
            'source_subpath' => $ex['source_subpath'] ?? '',
            'source_commit' => $ex['source_commit'] ?? '',
            'name' => $ex['name'] ?? '',
        ];
    }

    $mode = (string) ($generation['mode'] ?? 'dynamic');
    $updatesAutomatically = (bool) ($src['updates_automatically'] ?? false);

    return [
        'source_name' => (string) ($src['name'] ?? ''),
        'source_repository' => (string) ($src['source_repository'] ?? ''),
        'source_subpath' => (string) ($src['source_subpath'] ?? ''),
        'source_branch' => (string) ($src['source_branch'] ?? ''),
        'source_commit' => (string) ($src['source_commit'] ?? ''),
        'analyzed' => (string) ($src['analyzed'] ?? ''),
        'verification_scope' => (string) ($src['verification_scope'] ?? ''),
        'last_verified' => (string) ($src['last_verified'] ?? ''),
        'freshness_note' => (string) ($src['freshness_note'] ?? ''),
        'updates_automatically' => $updatesAutomatically,
        'mode' => $mode,
        'generated' => (string) ($generation['generated'] ?? ''),
        'generator_repository' => (string) ($generation['generator_repository'] ?? ''),
        'generator_commit' => (string) ($generation['generator_commit'] ?? ''),
        'generator_branch' => (string) ($generation['generator_branch'] ?? ''),
    ];
}

/**
 * Human label for a generation mode.
 */
function provenance_mode_label(string $mode): string
{
    return match ($mode) {
        'static' => 'Static snapshot',
        'dynamic' => 'Dynamic guide (rendered live from .vibekb/)',
        default => ucfirst($mode),
    };
}

/**
 * The one-sentence, objective freshness statement. This is the preferred
 * wording and must not overstate freshness.
 */
function provenance_disclaimer(array $p): string
{
    if (($p['mode'] ?? '') === 'static') {
        return 'This guide is a generated snapshot of the repository at the source commit shown below. '
            . 'Later repository changes may not be reflected until VibeKB is updated and regenerated.';
    }

    return 'This guide is rendered live from the repository-owned .vibekb/ content. '
        . 'It reflects that content as of the moment it was read, not any later source changes that '
        . 'have not yet been re-verified.';
}

/**
 * Render the provenance panel used on the Overview (and available to any view).
 * Objective labels only; every value that is present states its unit/meaning.
 *
 * @param array<string, string|bool> $p Normalised provenance (see provenance_data()).
 */
function provenance_panel(array $p): string
{
    $rows = [];
    $row = static function (string $label, string $value) use (&$rows): void {
        if (trim($value) === '') {
            return;
        }
        $rows[] = '<div class="provenance__row"><dt>' . h($label) . '</dt><dd>' . $value . '</dd></div>';
    };

    $row('Generation mode', h(provenance_mode_label((string) $p['mode'])));

    $repo = (string) $p['source_repository'];
    if ($repo !== '') {
        $sub = (string) $p['source_subpath'];
        $repoHtml = str_starts_with($repo, 'http')
            ? '<a href="' . h($repo) . '" rel="noopener noreferrer">' . h($repo) . '</a>'
            : h($repo);
        $row('Source repository', $repoHtml . ($sub !== '' ? ' <span class="muted">(' . h($sub) . ')</span>' : ''));
    }
    $row('Source branch', h((string) $p['source_branch']));
    $row('Source commit analyzed', '<code>' . h((string) $p['source_commit']) . '</code>');
    $row('Analysis generated', h((string) $p['generated']));
    $row('Verification scope', h((string) $p['verification_scope']));
    $row('Last verified against source', h((string) $p['last_verified']));

    if ((string) $p['mode'] === 'static' && (string) $p['generator_commit'] !== '') {
        $gen = '<code>' . h((string) $p['generator_commit']) . '</code>';
        if ((string) $p['generator_branch'] !== '') {
            $gen .= ' <span class="muted">(' . h((string) $p['generator_branch']) . ')</span>';
        }
        $row('Generated by VibeKB commit', $gen);
    }

    $row('Updates automatically', $p['updates_automatically'] ? 'Yes' : 'No — regenerate to refresh');

    $disclaimer = provenance_disclaimer($p);
    $note = (string) $p['freshness_note'];

    $html = '<section class="provenance wide-section" aria-labelledby="provenance-title">';
    $html .= '<h2 id="provenance-title" class="provenance__title">Provenance &amp; freshness</h2>';
    $html .= '<p class="provenance__disclaimer">' . h($disclaimer) . '</p>';
    if ($note !== '') {
        $html .= '<p class="provenance__note text-soft">' . h($note) . '</p>';
    }
    if ($rows !== []) {
        $html .= '<dl class="provenance__grid">' . implode('', $rows) . '</dl>';
    }
    $html .= '</section>';

    return $html;
}

/**
 * A compact one-line provenance stamp for page footers / generated-output
 * notices.
 */
function provenance_stamp(array $p): string
{
    $bits = [];
    if ((string) $p['mode'] === 'static') {
        $bits[] = 'Static snapshot';
        if ((string) $p['generated'] !== '') {
            $bits[] = 'generated ' . (string) $p['generated'];
        }
        if ((string) $p['source_commit'] !== '') {
            $bits[] = 'source commit ' . (string) $p['source_commit'];
        }
        $bits[] = 'not auto-updating';
    } else {
        $bits[] = 'Rendered live from .vibekb/';
    }
    return implode(' · ', array_map('h', $bits));
}
