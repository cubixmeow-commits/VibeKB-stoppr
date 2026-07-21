---
area: home-wellness
summary: Central hub with streak, widgets, panic button, and navigation to all major features.
status: implemented
verification: verified-from-source
user_facing: true
trigger: User opens Home tab (index 0) in MainScaffold.
files: [lib/features/app/presentation/screens/home_screen.dart]
reads: [shared_preferences, users]
writes: [shared_preferences]
depends_on: [main-navigation, sugar-streak-tracking]
related_memory: []
id: home-dashboard
type: functionality
title: Home dashboard
updated: 2026-07-21
---

## In one sentence

Central hub with streak, widgets, panic button, and navigation to all major features.

## Current behavior

Implemented in source per files listed in front matter. Runtime behavior depends on Firebase and API configuration.

## Current state

**Status:** implemented. **Verification:** verified-from-source.

## Safe to change

Presentation and copy with localization.

## Use caution

Data writes and subscription checks.
