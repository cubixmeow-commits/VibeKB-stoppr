---
id: relapse-recovery
type: functionality
title: Relapse recovery flow
area: home-wellness
summary: Relapse UI collects reason/help chips, resets streak, and may schedule recovery-challenge notifications; detailed reason chips are not fully persisted.
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
updated: 2026-07-23
tags: []
---

## In one sentence

Relapse flow reframes a setback and resets streak state.

## Current behavior

Screens collect why/help/target/signature in the UI. Persistence is thinner
than the UI suggests: signature/goal-day style fields and toast state are
kept, while reason/help chips are largely UI-local.
`RelapseService` stores relapse timestamps locally. Daily check-in can call
`logRelapse()`; the manual relapsed-flow path may reset streak without that
same service call. Notifications may be scheduled for a recovery challenge.

## Current state

Implemented; verified-from-source (re-checked 2026-07-23). Do not treat
chip selections as durable analytics unless re-verified.
