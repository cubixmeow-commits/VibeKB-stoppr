---
id: onboarding-profile-goals
type: functionality
title: Onboarding profile and goals
area: onboarding
summary: Collects name/age/gender, symptoms, and goals into Firestore onboarding subdocuments.
status: implemented
verification: verified-from-source
user_facing: true
trigger: After questionnaire / analysis path reaches profile and goals screens.
files: [lib/features/onboarding/presentation/screens/profile_info_screen.dart, lib/features/onboarding/presentation/screens/symptoms_screen.dart, lib/features/onboarding/presentation/screens/choose_goals_onboarding.dart]
reads: []
writes: []
config: []
depends_on: [onboarding-questionnaire, guest-anonymous-access]
related_memory: []
created: 2026-07-21
updated: 2026-07-23
tags: []
---

## In one sentence

ProfileInfoScreen may create anonymous auth, then symptoms and goals are saved.

## Current behavior

Writes `users/{uid}` profile fields and `onboarding/symptoms`,
`onboarding/goals`. Clears earlier progress after profile save, then continues
the funnel.

## Current state

Implemented; verified-from-source.
