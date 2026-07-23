---
id: work-handoff
type: work
title: Handoff
verification_state: verified-from-source
updated: 2026-07-23
---

## Current functionality state

Stoppr at commit `f01661b` (app `7.4.2+1`) is a Flutter sugar-habit app with
full onboarding, Firebase auth, Superwall/RevenueCat gating, home wellness
loops, community, nutrition AI tools, learn content, widgets, and platform
services. VibeKB runtime was upgraded from canonical VibeKB tip `3b6ba7d`
(installer template `1.0.0` / CLI `vibekb` built from that tip).

## Completed this session

- Full backup at `VibeKBbackup/pre-upgrade-2026-07-23/`.
- `vibekb install --upgrade` refreshed guide/tools/prompts/template/docs.
- Preserved Flutter `CLAUDE.md` + `flutter.mdc`; dual `AGENTS.md` bridge.
- Fresh source re-analysis; corrected drift on navigation, relapse, forum,
  food scan, and soft-paywalls records.
- Adapted `tools/test-topology.php` to discover local diagram fixtures
  (upstream test assumed VibeKB self-hosted diagram ids).
- Regenerated static `/docs`.

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
