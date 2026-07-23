---
id: onboarding-intro
type: functionality
title: Onboarding introduction
area: onboarding
summary: Welcome video and early onboarding pages introduce the product and present auth or skip options.
status: implemented
verification: verified-from-source
user_facing: true
trigger: First launch or incomplete onboarding without paid bypass.
files: [lib/features/onboarding/presentation/screens/welcome_video_screen.dart, lib/features/onboarding/presentation/screens/onboarding_page.dart, lib/features/onboarding/presentation/screens/onboarding_screen2.dart, lib/features/onboarding/presentation/screens/onboarding_screen3.dart]
reads: []
writes: []
config: []
depends_on: [startup-routing]
related_memory: []
created: 2026-07-21
updated: 2026-07-23
tags: []
---

## In one sentence

Welcome video hands off to `OnboardingPage` / early screens before questionnaire.

## Current behavior

`WelcomeVideoScreen` plays then navigates to the computed next screen.
`OnboardingPage` sequences FOMO/stats and auth options (Google/Apple/email/skip).

## Current state

Implemented; verified-from-source.
