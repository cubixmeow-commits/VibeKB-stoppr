---
area: home-wellness
summary: PanicFlowManager runs randomized coping tricks ending in sugary-treat education and congratulations.
status: implemented
verification: verified-from-source
user_facing: true
trigger: User taps panic button on home or via deep link stoppr://panic.
files: [lib/features/app/services/panic_flow_manager.dart, lib/features/app/presentation/screens/panic_button/what_happening_screen.dart]
reads: [shared_preferences]
writes: [shared_preferences]
depends_on: [home-dashboard, soft-paywalls-quotas]
related_memory: []
id: panic-intervention
type: functionality
title: Panic button intervention
updated: 2026-07-21
---

## In one sentence

PanicFlowManager runs randomized coping tricks ending in sugary-treat education and congratulations.

## Current behavior

Implemented in source per files listed in front matter. Runtime behavior depends on Firebase and API configuration.

## Current state

**Status:** implemented. **Verification:** verified-from-source.

## Safe to change

Presentation and copy with localization.

## Use caution

Data writes and subscription checks.
