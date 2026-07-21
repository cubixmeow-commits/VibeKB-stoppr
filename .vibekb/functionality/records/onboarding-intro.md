---
area: onboarding
summary: Welcome video and intro pages lead to auth or skip path into questionnaire.
status: implemented
verification: verified-from-source
user_facing: true
trigger: New user after WelcomeVideoScreen.
files: [lib/features/onboarding/presentation/screens/welcome_video_screen.dart, lib/features/onboarding/presentation/screens/onboarding_page.dart]
reads: [shared_preferences]
writes: [shared_preferences, users]
depends_on: [startup-routing]
related_memory: []
id: onboarding-intro
type: functionality
title: Onboarding introduction
updated: 2026-07-21
---

## In one sentence

Welcome video and intro pages lead to auth or skip path into questionnaire.

## Current behavior

Implemented in source per files listed in front matter. Runtime behavior depends on Firebase and API configuration.

## Current state

**Status:** implemented. **Verification:** verified-from-source.

## Safe to change

Presentation and copy with localization.

## Use caution

Data writes and subscription checks.
