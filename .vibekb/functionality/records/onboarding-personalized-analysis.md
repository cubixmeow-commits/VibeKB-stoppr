---
area: onboarding
summary: Shows analysis results, future-self letter, vow, ratings screen, and science-backed plan before paywall.
status: implemented
verification: verified-from-source
user_facing: true
trigger: User progresses through post-questionnaire benefit and analysis screens.
files: [lib/features/onboarding/presentation/screens/analysis_result_screen.dart, lib/features/onboarding/presentation/screens/letter_from_future_screen.dart, lib/features/onboarding/presentation/screens/read_the_vow_screen.dart]
reads: [shared_preferences]
writes: [shared_preferences, users]
depends_on: [onboarding-profile-goals]
related_memory: [assumption:onboarding-health-claims]
id: onboarding-personalized-analysis
type: functionality
title: Personalized analysis and commitment
updated: 2026-07-21
---

## In one sentence

Shows analysis results, future-self letter, vow, ratings screen, and science-backed plan before paywall.

## Current behavior

Implemented in source per files listed in front matter. Runtime behavior depends on Firebase and API configuration.

## Current state

**Status:** implemented. **Verification:** verified-from-source.

## Safe to change

Presentation and copy with localization.

## Use caution

Data writes and subscription checks.
