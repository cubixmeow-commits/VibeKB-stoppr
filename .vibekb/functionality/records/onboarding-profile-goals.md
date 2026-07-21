---
area: onboarding
summary: Collects demographics, symptoms, sugar pain points, and goals; creates anonymous user if needed.
status: implemented
verification: verified-from-source
user_facing: true
trigger: User completes questionnaire and proceeds through profile screens.
files: [lib/features/onboarding/presentation/screens/profile_info_screen.dart, lib/features/onboarding/presentation/screens/symptoms_screen.dart, lib/features/onboarding/presentation/screens/choose_goals_onboarding.dart]
reads: [users]
writes: [users, shared_preferences]
depends_on: [onboarding-questionnaire, guest-anonymous-access]
related_memory: []
id: onboarding-profile-goals
type: functionality
title: Profile, symptoms, and goals
updated: 2026-07-21
---

## In one sentence

Collects demographics, symptoms, sugar pain points, and goals; creates anonymous user if needed.

## Current behavior

Implemented in source per files listed in front matter. Runtime behavior depends on Firebase and API configuration.

## Current state

**Status:** implemented. **Verification:** verified-from-source.

## Safe to change

Presentation and copy with localization.

## Use caution

Data writes and subscription checks.
