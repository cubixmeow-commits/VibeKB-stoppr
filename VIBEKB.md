# VibeKB for Stoppr

This repository includes a [VibeKB](https://github.com/cubixmeow-commits/VibeKB)
living software model and a static guide in `/docs`.

## Quick links

- **Guide entry point:** [`/docs/index.html`](docs/index.html)
- **Content model:** [`.vibekb/`](.vibekb/)
- **Maintenance:** [`MAINTENANCE.md`](MAINTENANCE.md) · [`VIBEKB_MAINTENANCE.md`](VIBEKB_MAINTENANCE.md)
- **Schema / product:** [`SCHEMA.md`](SCHEMA.md) · [`PRODUCT.md`](PRODUCT.md)
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
php tools/validate.php
php tools/test-topology.php
php tools/generate-static.php
```

## GitHub Pages

1. Repository **Settings → Pages**
2. Source: branch `main` (or the publishing branch)
3. Folder: `/docs`
4. Save

Site URL shape: `https://<owner>.github.io/VibeKB-stoppr/`

## Scope

VibeKB documents the Stoppr Flutter app. It does **not** modify application
code. Allowed documentation surfaces: `.vibekb/`, `guide/`, `tools/`, `/docs`,
and VibeKB authoring files (`PRODUCT.md`, `SCHEMA.md`, `INITIALIZE.md`,
`MAINTENANCE.md`, this file).

Flutter app agent rules remain in [`CLAUDE.md`](CLAUDE.md). VibeKB agent short
rules: [`AGENTS.md`](AGENTS.md).

## Backup

Before a rebootstrap, existing `.vibekb/` and `docs/` were copied to
`VibeKBbackup/` (do not modify that backup).
