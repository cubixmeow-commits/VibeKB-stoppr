---
area: platform
summary: NotificationService schedules FCM and local reminders with subscription-aware types and daily caps.
status: implemented
verification: verified-from-source
user_facing: true
trigger: App init, user settings, subscription changes, scheduled times.
files: [lib/core/notifications/notification_service.dart, lib/main.dart]
reads: [shared_preferences, users]
writes: [shared_preferences, users]
depends_on: [app-startup, subscription-access-gating]
related_memory: [discovery:disabled-notification-types]
id: notifications
type: functionality
title: Push and local notifications
updated: 2026-07-21
---

## In one sentence

NotificationService schedules FCM and local reminders with subscription-aware types and daily caps.

## Current behavior

Implemented in source per files listed in front matter. Runtime behavior depends on Firebase and API configuration.

## Current state

**Status:** implemented. **Verification:** verified-from-source.

## Safe to change

Presentation and copy with localization.

## Use caution

Data writes and subscription checks.
