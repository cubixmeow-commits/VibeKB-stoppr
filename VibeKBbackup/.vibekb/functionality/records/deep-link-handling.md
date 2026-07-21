---
area: app-core
summary: Routes payment success, panic, pledge, meditation, and share-invite URIs to the correct screens.
status: implemented
verification: verified-from-source
user_facing: true
trigger: App receives URI via app_links or AppsFlyer forwarding.
files: [lib/main.dart, lib/core/streak/sharing_service.dart, lib/core/analytics/appsflyer_service.dart]
reads: [sharing_tokens, users]
writes: [users]
depends_on: [app-startup]
related_memory: []
id: deep-link-handling
type: functionality
title: Deep link handling
updated: 2026-07-21
---

## In one sentence

Routes payment success, panic, pledge, meditation, and share-invite URIs to the correct screens.

## Current behavior

Implemented in source per files listed in front matter. Runtime behavior depends on Firebase and API configuration.

## Current state

**Status:** implemented. **Verification:** verified-from-source.

## Safe to change

Presentation and copy with localization.

## Use caution

Data writes and subscription checks.
