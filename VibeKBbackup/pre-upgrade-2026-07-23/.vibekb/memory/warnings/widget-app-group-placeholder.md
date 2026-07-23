---
id: widget-app-group-placeholder
type: warning
title: iOS widget app group ID is a placeholder
summary: StreakService uses group.YOUR_BUNDLE_ID.shared for HomeWidget sync.
severity: medium
verification: verified-from-source
functionality: [sugar-streak-tracking, home-widgets]
files: [lib/core/streak/streak_service.dart, ios/StreakWidgetExtension.entitlements]
updated: 2026-07-21
---

## What can go wrong

iOS widgets will not receive streak/accountability updates until the real app
group is configured in Xcode and code.

## Safe next action

Set the real app group id in Flutter + entitlements; verify widget refresh.
