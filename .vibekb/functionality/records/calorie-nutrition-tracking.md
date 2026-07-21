---
area: nutrition
summary: Separate nutrition module logs meals to Firestore with Edamam API and onboarding sub-flow.
status: implemented
verification: verified-from-source
user_facing: true
trigger: User opens Calorie Tracker from home.
files: [lib/features/nutrition/data/repositories/nutrition_repository.dart, lib/features/nutrition/presentation/screens/calorie_tracker_dashboard.dart]
reads: [food_logs, daily_summaries, nutrition_profile, users]
writes: [food_logs, daily_summaries, nutrition_profile, users]
depends_on: [home-dashboard, guest-anonymous-access]
related_memory: []
id: calorie-nutrition-tracking
type: functionality
title: Calorie and nutrition tracking
updated: 2026-07-21
---

## In one sentence

Separate nutrition module logs meals to Firestore with Edamam API and onboarding sub-flow.

## Current behavior

Implemented in source per files listed in front matter. Runtime behavior depends on Firebase and API configuration.

## Current state

**Status:** implemented. **Verification:** verified-from-source.

## Safe to change

Presentation and copy with localization.

## Use caution

Data writes and subscription checks.
