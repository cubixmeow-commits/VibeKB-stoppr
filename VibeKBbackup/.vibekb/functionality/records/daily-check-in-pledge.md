---
area: home-wellness
summary: DailyCheckInWidget and PledgeService handle mood check-in, relapse flow, and 24-hour pledges.
status: implemented
verification: verified-from-source
user_facing: true
trigger: Home screen overlay or pledge notification.
files: [lib/features/app/presentation/widgets/daily_check_in_widget.dart, lib/core/pledge/pledge_service.dart]
reads: [shared_preferences, users]
writes: [shared_preferences, users]
depends_on: [home-dashboard, sugar-streak-tracking]
related_memory: [discovery:check-in-on-load-disabled]
id: daily-check-in-pledge
type: functionality
title: Daily check-in and pledge
updated: 2026-07-21
---

## In one sentence

DailyCheckInWidget and PledgeService handle mood check-in, relapse flow, and 24-hour pledges.

## Current behavior

Implemented in source per files listed in front matter. Runtime behavior depends on Firebase and API configuration.

## Current state

**Status:** implemented. **Verification:** verified-from-source.

## Safe to change

Presentation and copy with localization.

## Use caution

Data writes and subscription checks.
