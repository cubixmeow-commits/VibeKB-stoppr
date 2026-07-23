---
id: home-dashboard
type: functionality
title: Home dashboard
area: home-wellness
summary: Central dashboard orchestrating streak display, check-in widgets, panic entry, banners, and feature shortcuts.
status: implemented
verification: verified-from-source
user_facing: true
trigger: User opens the Home tab in MainScaffold.
files: [lib/features/app/presentation/screens/home_screen.dart]
reads: []
writes: []
config: []
depends_on: [main-navigation, sugar-streak-tracking]
related_memory: []
created: 2026-07-21
updated: 2026-07-21
tags: []
---

## In one sentence

`HomeScreen` is the daily hub for wellness actions and subscription banners.

## Current behavior

Composes streak UI, pledge/check-in widgets, panic entry, challenge links, and
Superwall banner triggers for trial/cancel scenarios.

## Current state

Implemented; verified-from-source.
