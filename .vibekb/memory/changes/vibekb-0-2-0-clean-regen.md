---
id: change-vibekb-0-2-0-clean-regen
type: change
title: Clean VibeKB 0.2.0 Stoppr model regeneration
summary: Regenerated the Stoppr living model and /docs as a clean 0.2.0 analysis with canonical important-files schema and current provenance.
status: completed
verification: verified-from-source
functionality: []
files: [.vibekb/manifest.json, .vibekb/files/important-files.json, .vibekb/work/current.md, .vibekb/work/handoff.md, docs/]
created: 2026-07-23
updated: 2026-07-23
---

## Before

- `/docs` Key Files reported `0` and Files That Matter was empty because
  `important-files.json` used a bare JSON array from the pre-0.2.0 migration.
- Provenance still pointed at commit `f01661b` and described a migrate-without-
  re-trace session.

## After

- Fresh `important-files.json` in `{"files":[...]}` shape with 31 curated
  files re-verified against source.
- Provenance and work/handoff updated to commit `2edc099`.
- Loader now errors on a bare-array important-files file.
- `/docs` fully regenerated from `.vibekb/` via the current runtime.

## Impact

Documentation accuracy only. No Stoppr application code under `lib/` changed.
