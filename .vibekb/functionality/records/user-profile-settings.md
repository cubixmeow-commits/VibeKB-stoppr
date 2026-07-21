---
area: platform
summary: Profile tab for account management, notification prefs, language, sign-out, and account deletion.
status: implemented
verification: inferred-from-source
user_facing: true
trigger: User opens Profile tab (index 4).
files: [lib/features/app/presentation/screens/user_profile_screen.dart]
reads: [users, shared_preferences]
writes: [users, shared_preferences]
depends_on: [main-navigation, firebase-auth]
related_memory: []
id: user-profile-settings
type: functionality
title: User profile and settings
updated: 2026-07-21
---

## In one sentence

Profile tab for account management, notification prefs, language, sign-out, and account deletion.

## Current behavior

Implemented in source per files listed in front matter. Runtime behavior depends on Firebase and API configuration.

## Current state

**Status:** implemented. **Verification:** inferred-from-source.

## Safe to change

Presentation and copy with localization.

## Use caution

Data writes and subscription checks.
