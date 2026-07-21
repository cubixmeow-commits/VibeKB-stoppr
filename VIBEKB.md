# VibeKB for Stoppr

This repository includes a [VibeKB](https://github.com/cubixmeow-commits/VibeKB) living software model and a static guide generated from it.

## Quick links

- **Guide entry point:** [`/docs/index.html`](docs/index.html)
- **Content model:** [`.vibekb/`](.vibekb/)
- **Maintenance:** [`VIBEKB_MAINTENANCE.md`](VIBEKB_MAINTENANCE.md)

## What VibeKB is

VibeKB explains what Stoppr **currently does** — organized around functionality (behaviors), not file dumps. Each record includes status, verification level, and source evidence.

## Regenerate the guide

After editing `.vibekb/` content:

```bash
python3 .vibekb/tools/generate_docs.py
```

This writes static HTML to `/docs/`. No PHP or build tools required to **read** the guide.

## GitHub Pages

1. Repository **Settings → Pages**
2. Source: branch `main`
3. Folder: `/docs`
4. Save

The site will be available at `https://<owner>.github.io/VibeKB-stoppr/` (or your configured Pages URL).

## Scope

VibeKB documents the Stoppr Flutter app. It does **not** modify application code. Only `.vibekb/`, `docs/`, and VibeKB maintenance docs are part of this integration.
