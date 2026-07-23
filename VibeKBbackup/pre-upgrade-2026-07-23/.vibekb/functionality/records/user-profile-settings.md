---
id: user-profile-settings
type: functionality
title: User profile and settings
area: platform
summary: Profile editing, notification preferences, and RevenueCat Customer Center for subscription management.
status: implemented
verification: verified-from-source
user_facing: true
trigger: User opens profile/settings screens.
files: [lib/features/app/presentation/screens/profile/user_profile_screen.dart, lib/features/app/presentation/screens/profile/settings/user_profile_details.dart, lib/features/app/presentation/screens/profile/settings/cancel_subscription_screen.dart]
reads: []
writes: []
config: []
depends_on: [firebase-auth, subscription-access-gating]
related_memory: []
created: 2026-07-21
updated: 2026-07-21
tags: []
---

## In one sentence

Active cancel/manage path uses RevenueCatUI Customer Center.

## Current behavior

`user_profile_details.dart` presents Customer Center. Instruction-only
`CancelSubscriptionScreen` and legacy `UnsubscribeScreen` exist; callers for
legacy paths were not found. Theme/notification prefs use SharedPreferences.

## Current state

Implemented; verified-from-source.
