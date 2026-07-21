---
id: sugar-streak-tracking
type: functionality
title: Sugar streak tracking
area: home-wellness
summary: Persists streak counters in SharedPreferences and Firestore and syncs values to home widgets.
status: implemented
verification: verified-from-source
user_facing: true
trigger: App open, check-in success/failure, relapse, or post-purchase init.
files: [lib/core/streak/streak_service.dart, lib/core/streak/app_open_streak_service.dart, lib/core/streak/achievements_service.dart]
reads: []
writes: []
config: []
depends_on: [subscription-access-gating]
related_memory: [warning:widget-app-group-placeholder]
created: 2026-07-21
updated: 2026-07-21
tags: []
---

## In one sentence

StreakService owns streak persistence and widget updates.

## Current behavior

Reads/writes user streak fields locally and on `users/{uid}`, updates
`HomeWidget` data, and participates in relapse resets and post-purchase init.

## Current state

Implemented; widget sync partial due to app group placeholder.
Verified-from-source.
