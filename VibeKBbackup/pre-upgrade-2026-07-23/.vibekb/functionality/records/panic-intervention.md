---
id: panic-intervention
type: functionality
title: Panic intervention
area: home-wellness
summary: Guided multi-step panic flow with randomized tricks and breathing/meditation helpers.
status: implemented
verification: verified-from-source
user_facing: true
trigger: User taps Panic from home, widget deep link, or notification.
files: [lib/features/app/services/panic_flow_manager.dart, lib/features/app/presentation/screens/panic_button/what_happening_screen.dart]
reads: []
writes: []
config: []
depends_on: [home-dashboard]
related_memory: []
created: 2026-07-21
updated: 2026-07-21
tags: []
---

## In one sentence

`PanicFlowManager` sequences intervention screens for craving moments.

## Current behavior

Starts at `WhatHappeningScreen` and walks trick/breathing/meditation steps.
Deep link `stoppr://panic` routes here.

## Current state

Implemented; verified-from-source.
