---
id: relapse-recovery
type: functionality
title: Relapse recovery flow
area: home-wellness
summary: Captures relapse reasons and targets, resets streak, and schedules relapse-challenge notifications.
status: implemented
verification: verified-from-source
user_facing: true
trigger: User indicates relapse from check-in or related entry points.
files: [lib/core/relapse/relapse_service.dart, lib/features/app/presentation/screens/relapsed_flow/relapse_why_screen.dart]
reads: []
writes: []
config: []
depends_on: [daily-check-in-pledge, sugar-streak-tracking]
related_memory: []
created: 2026-07-21
updated: 2026-07-21
tags: []
---

## In one sentence

Relapse flow reframes a setback and resets streak state.

## Current behavior

Screens collect why/help/target/signature; `RelapseService` persists locally;
notifications may be scheduled for a recovery challenge.

## Current state

Implemented; verified-from-source.
