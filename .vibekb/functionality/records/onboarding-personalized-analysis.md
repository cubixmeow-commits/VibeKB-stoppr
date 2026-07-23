---
id: onboarding-personalized-analysis
type: functionality
title: Onboarding personalized analysis
area: onboarding
summary: Animated analysis screens plus nutrition onboarding compute calorie goals before benefits and paywall.
status: implemented
verification: verified-from-source
user_facing: true
trigger: Profile completion when questionnaire answers exist, then nutrition onboarding.
files: [lib/features/onboarding/presentation/screens/calculating_screen.dart, lib/features/onboarding/presentation/screens/analysis_result_screen.dart, lib/features/nutrition/presentation/onboarding/screens/results_calories_onboarding_screen.dart]
reads: []
writes: []
config: []
depends_on: [onboarding-profile-goals]
related_memory: []
created: 2026-07-21
updated: 2026-07-23
tags: []
---

## In one sentence

Calculating/analysis UI precedes nutrition goal setup and benefits pages.

## Current behavior

`CalculatingScreen` → `AnalysisResultScreen` → symptoms continuation; nutrition
onboarding screens compute and store daily goals under
`users/{uid}/nutrition_profile/daily_goals` and body metrics.

## Current state

Implemented; verified-from-source. Medical accuracy of copy is out of scope.
