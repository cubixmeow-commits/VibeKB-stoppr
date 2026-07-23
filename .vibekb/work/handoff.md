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
services. VibeKB runtime is **0.2.0** (CLI tip `683df05`, installer template
`2.0.0`) in the consolidated layout under `.vibekb/`.

## Completed this session

- Ran `vibekb migrate` from the pre-0.2.0 root layout (tip `3b6ba7d`).
- Runtime / reference / prompts now live under `.vibekb/runtime/`,
  `.vibekb/reference/`, and `.vibekb/prompts/`.
- Preserved Flutter `CLAUDE.md` + Stoppr `AGENTS.md`; VibeKB managed blocks
  added; namespaced `.cursor/rules/vibekb.mdc` refreshed.
- Removed unmodified root VibeKB docs/runtime leftovers; updated Stoppr
  pointers (`VIBEKB.md`, `VIBEKB_MAINTENANCE.md`, `AGENTS.md`).
- Kept Mode B GitHub Pages at `/docs` (local runtime preference when
  `docs/index.html` exists).
- Re-adapted topology test to discover local diagram fixtures.
- Regenerated static `/docs`.

## Still open / landmines

- Superwall `INSERT_YOUR_*` placements.
- Widget app group placeholder (`group.YOUR_BUNDLE_ID.shared`).
- Android Superwall purchase controller asymmetry.
- `.env` vs `.env.local` naming mismatch.
- Quota system disabled flags (`QUOTA_SYSTEM_ENABLED = false`).
- Incomplete community blocked-user filtering on Cubit stream.
- Unhandled `stoppr://accountability` deep link from widgets.
- Local adaptations pending upstream: topology carrier discovery; Mode B
  `/docs` default for Pages-hosted target installs.

## Exact next recommended action

Replace Superwall placeholder placement IDs (or confirm dashboard aliases)
and re-verify a sandbox purchase on **both** iOS and Android, then update
`main-paywall` / `soft-paywalls-quotas` verification and regenerate `/docs`.
