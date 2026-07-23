---
id: work-handoff
type: work
title: Handoff
verification_state: verified-from-source
updated: 2026-07-23
---

## Current functionality state

Stoppr at commit `2edc099` (app `7.4.2+1`) is a Flutter sugar-habit app with
full onboarding, Firebase auth, Superwall/RevenueCat gating, home wellness
loops, community, nutrition AI tools, learn content, widgets, and platform
services. The living model is a **clean VibeKB 0.2.0** analysis (installer
`0.2.0`, template `2.0.0`) under `.vibekb/`, with Mode B `/docs` regenerated
from that model.

## Completed this session

- Deleted previous generated `/docs` (and did not reuse prior HTML).
- Re-traced Stoppr source under `lib/` and refreshed project/system/work
  records and provenance to commit `2edc099`.
- Rebuilt `.vibekb/files/important-files.json` in the canonical 0.2.0
  `{"files":[...]}` shape (31 curated files).
- Taught the loader to error on a bare-array important-files file instead of
  silently showing zero Key Files.
- Regenerated static `/docs` from the current runtime templates.

## Still open / landmines

- Superwall `INSERT_YOUR_*` placements.
- Widget app group placeholder (`group.YOUR_BUNDLE_ID.shared`).
- Android Superwall purchase controller asymmetry.
- `.env` vs `.env.local` naming mismatch.
- Quota system disabled flags (`QUOTA_SYSTEM_ENABLED = false`).
- Incomplete community blocked-user filtering on Cubit stream.
- Unhandled `stoppr://accountability` deep link from widgets.

## Exact next recommended action

Replace Superwall placeholder placement IDs (or confirm dashboard aliases)
and re-verify a sandbox purchase on **both** iOS and Android, then update
`main-paywall` / `soft-paywalls-quotas` verification and regenerate `/docs`.
