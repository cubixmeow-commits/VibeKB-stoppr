---
area: home-wellness
summary: StreakService maintains a live sugar-free timer synced to SharedPreferences and Firestore for subscribers.
status: implemented
verification: verified-from-source
user_facing: true
trigger: App launch and home screen display.
files: [lib/core/streak/streak_service.dart, lib/features/app/presentation/widgets/streak_counter_widget.dart]
reads: [shared_preferences, users]
writes: [shared_preferences, users]
depends_on: [app-startup]
related_memory: [warning:widget-app-group-placeholder]
id: sugar-streak-tracking
type: functionality
title: Sugar-free streak tracking
updated: 2026-07-21
---

## In one sentence

StreakService maintains a live sugar-free timer synced to SharedPreferences and Firestore for subscribers.

## Current behavior

Implemented in source per files listed in front matter. Runtime behavior depends on Firebase and API configuration.

## Current state

**Status:** implemented. **Verification:** verified-from-source.

## Safe to change

Presentation and copy with localization.

## Use caution

Data writes and subscription checks.
