---
id: analytics-telemetry
type: functionality
title: Analytics and telemetry
area: platform
summary: Mixpanel primary product analytics, AppsFlyer attribution, Crashlytics error reporting, Firebase Analytics purchase events.
status: implemented
verification: verified-from-source
user_facing: false
trigger: App events throughout user journeys.
files: [lib/core/analytics/mixpanel_service.dart, lib/core/analytics/appsflyer_service.dart, lib/core/analytics/crashlytics_service.dart]
reads: []
writes: []
config: []
depends_on: [app-startup]
related_memory: []
created: 2026-07-21
updated: 2026-07-23
tags: []
---

## In one sentence

Telemetry SDKs initialize at startup and receive identity sync from AuthService.

## Current behavior

Uses config names `MIXPANEL_API_KEY`, `APPSFLYER_*`. Crashlytics filters wrap
reporting. Do not document secret values.

## Current state

Implemented; verified-from-source.
