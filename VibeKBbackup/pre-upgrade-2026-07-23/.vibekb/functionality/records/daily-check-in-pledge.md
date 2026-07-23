---
id: daily-check-in-pledge
type: functionality
title: Daily check-in and pledge
area: home-wellness
summary: Daily pledge/check-in records success or failure with feelings/notes and can reset streak on relapse indication.
status: implemented
verification: verified-from-source
user_facing: true
trigger: User interacts with daily check-in or pledge widgets/screens.
files: [lib/core/pledges/pledge_service.dart, lib/features/app/presentation/widgets/daily_check_in_widget.dart, lib/features/app/presentation/screens/pledge_screen.dart]
reads: []
writes: []
config: []
depends_on: [home-dashboard, sugar-streak-tracking]
related_memory: []
created: 2026-07-21
updated: 2026-07-21
tags: []
---

## In one sentence

Pledges store under `users/{uid}/pledges` and local preferences.

## Current behavior

`PledgeService` manages pledge lifecycle; UI widgets collect outcomes. Failure
paths can trigger relapse-related streak resets.

## Current state

Implemented; verified-from-source.
