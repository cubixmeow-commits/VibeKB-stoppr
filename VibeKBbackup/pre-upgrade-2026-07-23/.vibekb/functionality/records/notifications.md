---
id: notifications
type: functionality
title: Notifications
area: platform
summary: Local notifications and FCM with preference storage; some payloads map to placeholder Superwall placements.
status: implemented
verification: verified-from-source
user_facing: true
trigger: Permission grant, schedule points, FCM messages, or preference changes.
files: [lib/core/notifications/notification_service.dart]
reads: []
writes: []
config: []
depends_on: [app-startup]
related_memory: [warning:superwall-placement-placeholders]
created: 2026-07-21
updated: 2026-07-21
tags: []
---

## In one sentence

NotificationService owns channels, scheduling, and preference keys.

## Current behavior

Covers streak, motivation, meals, fasting, relapse challenge, trial, chat, and
more. Platform branches for iOS vs Android. Some notification payloads still
reference `INSERT_YOUR_*` Superwall placements.

## Current state

Implemented with placement-config risk; verified-from-source.
