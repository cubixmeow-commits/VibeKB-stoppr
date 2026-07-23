---
id: home-widgets
type: functionality
title: Home screen widgets
area: app-core
summary: Flutter home_widget bridge syncs streak and accountability data to iOS WidgetKit and Android AppWidgets; app group id is still a placeholder.
status: partial
verification: verified-from-source
user_facing: true
trigger: OS shows Streak/Pledge/Panic/Meditation/Accountability widgets.
files: [lib/core/streak/streak_service.dart, lib/core/accountability/accountability_widget_service.dart, ios/StreakWidget/StreakWidgetBundle.swift, android/app/src/main/AndroidManifest.xml]
reads: []
writes: []
config: []
depends_on: [sugar-streak-tracking]
related_memory: [warning:widget-app-group-placeholder]
created: 2026-07-21
updated: 2026-07-23
tags: []
---

## In one sentence

Widgets read shared defaults written by Flutter, but the iOS app group id is
still `group.YOUR_BUNDLE_ID.shared`.

## Current behavior

`StreakService` and `AccountabilityWidgetService` call `HomeWidget.setAppGroupId`
and update widget data. iOS Swift widgets and Android Kotlin providers are
present and registered.

## Current state

Partial (implementation exists; shared group placeholder blocks real iOS sync
until configured). Verification: verified-from-source.
