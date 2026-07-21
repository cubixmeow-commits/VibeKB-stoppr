---
id: fasting-tracker
type: functionality
title: Fasting tracker
area: wellness-tools
summary: Local-only fasting logs in SharedPreferences with scheduled reminder notifications.
status: implemented
verification: verified-from-source
user_facing: true
trigger: User configures or monitors a fast.
files: [lib/features/fasting/data/repositories/fasting_repository.dart, lib/features/fasting/presentation/screens/fasting_dashboard_screen.dart]
reads: []
writes: []
config: []
depends_on: [notifications]
related_memory: []
created: 2026-07-21
updated: 2026-07-21
tags: []
---

## In one sentence

Fasting state is not Firestore-backed in the repository reviewed.

## Current behavior

`fasting_logs_v1` preference key; NotificationService schedules/cancels
reminders.

## Current state

Implemented; verified-from-source.
