# VibeKB for Stoppr

This repository includes a [VibeKB](https://github.com/cubixmeow-commits/VibeKB)
living software model and a static guide in `/docs`.

## Quick links

- **Guide entry point:** [`/docs/index.html`](docs/index.html)
- **Content model:** [`.vibekb/`](.vibekb/)
- **Maintenance:** [`MAINTENANCE.md`](MAINTENANCE.md) · [`VIBEKB_MAINTENANCE.md`](VIBEKB_MAINTENANCE.md)
- **Schema / product:** [`SCHEMA.md`](SCHEMA.md) · [`PRODUCT.md`](PRODUCT.md)
- **Installer:** [`INSTALLER.md`](INSTALLER.md)
- **Initialize / integrate:** [`INITIALIZE.md`](INITIALIZE.md) ·
  [`prompts/INTEGRATE_VIBEKB.md`](prompts/INTEGRATE_VIBEKB.md)

## What VibeKB is

VibeKB explains what Stoppr **currently does** — organized around functionality
(behaviors), not file dumps. Each record includes status, verification level,
and source evidence. Explainable Diagrams add topologies so every node, edge,
and file states why it exists.

The `/docs` folder is a **generated** static snapshot for GitHub Pages.
`.vibekb/` is the source of truth. Do not hand-edit generated HTML.

## Regenerate

```bash
php tools/vibekb.php check
php tools/test-topology.php
php tools/vibekb.php generate
```

(Legacy equivalents: `php tools/validate.php`,
`php tools/generate-static.php`.)

## Upgrade

```bash
# Install/refresh the vibekb CLI, then:
vibekb install --upgrade .
```

Preserves `.vibekb/`. Use `--force` only to reset the model.

## GitHub Pages

1. Repository **Settings → Pages**
2. Source: branch `main` (or the publishing branch)
3. Folder: `/docs`
4. Save

Site URL shape: `https://<owner>.github.io/VibeKB-stoppr/`

## Scope

VibeKB documents the Stoppr Flutter app. It does **not** modify application
code. Allowed documentation surfaces: `.vibekb/`, `guide/`, `tools/`,
`template/starter/`, `/docs`, `prompts/`, and VibeKB authoring files
(`PRODUCT.md`, `SCHEMA.md`, `INITIALIZE.md`, `MAINTENANCE.md`,
`INSTALLER.md`, this file).

Flutter app agent rules remain in [`CLAUDE.md`](CLAUDE.md). Combined agent
entry: [`AGENTS.md`](AGENTS.md).

## Backup

Complete pre-upgrade backups live under `VibeKBbackup/`:

- `VibeKBbackup/pre-upgrade-2026-07-23/` — full snapshot before the
  2026-07-23 upgrade (`.vibekb/`, `docs/`, `guide/`, `tools/`, `prompts/`,
  authoring docs, `.cursor/rules`).
- Older partial copies may also exist under `VibeKBbackup/` (do not modify).
