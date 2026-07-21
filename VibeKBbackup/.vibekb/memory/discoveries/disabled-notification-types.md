---
id: disabled-notification-types
type: discovery
title: Some notification types explicitly removed
summary: App update and breakfast reminder notifications are disabled in NotificationService.
changed_model: notifications
verification: verified-from-source
functionality: [notifications]
files: [lib/core/notifications/notification_service.dart]
updated: 2026-07-21
---

## Evidence

Comments: "removed per request" for app update notifications; breakfast reminder keys commented out.

## Impact

Users cannot enable these notification categories even if UI remnants exist.
