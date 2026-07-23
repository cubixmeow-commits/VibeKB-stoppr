---
id: change-vibekb-upgrade-2026-07-23
type: change
title: Upgrade VibeKB runtime and re-analyze Stoppr
date: 2026-07-23
affects: [app-startup, main-navigation, relapse-recovery, community-forum, sugar-food-scanning, soft-paywalls-quotas]
verification: verified-from-source
---

## What changed

Upgraded the Stoppr repository from the prior manually integrated VibeKB
snapshot (content model dated 2026-07-21, pre-installer) to the current
VibeKB implementation from `cubixmeow-commits/VibeKB` tip `3b6ba7d`:

- Native installer payload (`guide/`, `tools/` including `vibekb.php`,
  `template/starter/`, `prompts/`, `INSTALLER.md`, `.cursor/rules/vibekb.mdc`)
- Fresh Stoppr analysis against commit `f01661b` (lib/ unchanged since prior
  rebootstrap; claim drift corrected where re-trace found gaps)
- Regenerated Mode B `/docs`

## Why

Owner requested upgrade to the latest VibeKB and a fresh analysis so the
living model and generated guide match the newest runtime.

## Backup

`VibeKBbackup/pre-upgrade-2026-07-23/` holds the complete prior knowledge,
docs, config, and generated surfaces for restore.
