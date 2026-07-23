# VibeKB for Stoppr

This repository includes a [VibeKB](https://github.com/cubixmeow-commits/VibeKB)
living software model (0.2.0 safe layout) and a static guide in `/docs`.

## Quick links

- **Guide entry point:** [`/docs/index.html`](docs/index.html)
- **Content model:** [`.vibekb/`](.vibekb/)
- **Runtime / CLI:** [`.vibekb/runtime/`](.vibekb/runtime/)
- **Reference docs:** [`.vibekb/reference/`](.vibekb/reference/)
- **Integrate prompt:** [`.vibekb/prompts/INTEGRATE_VIBEKB.md`](.vibekb/prompts/INTEGRATE_VIBEKB.md)
- **Cursor rule:** [`.cursor/rules/vibekb.mdc`](.cursor/rules/vibekb.mdc)

## What VibeKB is

VibeKB explains what Stoppr **currently does** — organized around functionality
(behaviors), not file dumps. Each record includes status, verification level,
and source evidence. Explainable Diagrams add topologies so every node, edge,
and file states why it exists.

The `/docs` folder is a **generated** static snapshot for GitHub Pages.
`.vibekb/` is the source of truth. Do not hand-edit generated HTML.

VibeKB 0.2.0 keeps its runtime, reference docs, and prompts under `.vibekb/`
and only touches `AGENTS.md` / `CLAUDE.md` via a marked managed block.

## Regenerate

```bash
vibekb check
php .vibekb/runtime/tools/test-topology.php
vibekb generate
```

## Upgrade

```bash
# Install/refresh the vibekb CLI (v0.2.0+), then:
vibekb install --upgrade .
# From a pre-0.2.0 root layout:
vibekb migrate .
```

Preserves `.vibekb/` model records. Use `--force` only to reset the model.

## GitHub Pages

1. Repository **Settings → Pages**
2. Source: branch `main` (or the publishing branch)
3. Folder: `/docs`
4. Save

Site URL shape: `https://<owner>.github.io/VibeKB-stoppr/`

## Scope

VibeKB documents the Stoppr Flutter app. It does **not** modify application
code. Allowed documentation surfaces: `.vibekb/` (model + runtime + reference),
`/docs` (generated), namespaced adapters (`.cursor/rules/vibekb.mdc`), and the
managed blocks in `AGENTS.md` / `CLAUDE.md`.

Flutter app agent rules remain in [`CLAUDE.md`](CLAUDE.md). Combined agent
entry: [`AGENTS.md`](AGENTS.md).

## Backup

Complete pre-upgrade backups live under `VibeKBbackup/`:

- `VibeKBbackup/pre-upgrade-2026-07-23/` — full snapshot before the
  2026-07-23 upgrade (`.vibekb/`, `docs/`, `guide/`, `tools/`, `prompts/`,
  authoring docs, `.cursor/rules`).
- Older partial copies may also exist under `VibeKBbackup/` (do not modify).
