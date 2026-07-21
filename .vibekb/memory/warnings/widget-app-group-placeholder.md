---
id: widget-app-group-placeholder
type: warning
title: iOS home widget app group ID is a placeholder
summary: StreakService references group.YOUR_BUNDLE_ID.shared for home widget sync.
severity: medium
verification: verified-from-source
functionality: [sugar-streak-tracking]
files: [lib/core/streak/streak_service.dart]
updated: 2026-07-21
---

## What can go wrong

iOS home widget streak display will not sync until the real app group identifier is configured in Xcode and code.
