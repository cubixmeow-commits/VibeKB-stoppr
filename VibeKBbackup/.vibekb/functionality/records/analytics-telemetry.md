---
area: platform
summary: Mixpanel primary events, AppsFlyer attribution, Facebook purchase events, Crashlytics, screenshot tracking.
status: implemented
verification: verified-from-source
user_facing: false
trigger: Screen views, button taps, purchases, app lifecycle.
files: [lib/core/analytics/mixpanel_service.dart, lib/core/analytics/appsflyer_service.dart, lib/core/analytics/screenshot_tracker.dart]
reads: [users]
writes: []
config: [MIXPANEL_API_KEY, APPSFLYER_DEV_KEY]
depends_on: [app-startup]
related_memory: []
id: analytics-telemetry
type: functionality
title: Analytics and telemetry
updated: 2026-07-21
---

## In one sentence

Mixpanel primary events, AppsFlyer attribution, Facebook purchase events, Crashlytics, screenshot tracking.

## Current behavior

Implemented in source per files listed in front matter. Runtime behavior depends on Firebase and API configuration.

## Current state

**Status:** implemented. **Verification:** verified-from-source.

## Safe to change

Presentation and copy with localization.

## Use caution

Data writes and subscription checks.
