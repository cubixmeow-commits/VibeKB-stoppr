---
area: authentication
summary: Google, Apple, and email/password sign-in with AuthCubit state machine and RevenueCat identity sync.
status: implemented
verification: verified-from-source
user_facing: true
trigger: User taps sign-in on onboarding or profile.
files: [lib/core/auth/auth_service.dart, lib/core/auth/cubit/auth_cubit.dart, lib/features/auth/presentation/screens/email_auth_screen.dart]
reads: [users]
writes: [users]
depends_on: [app-startup]
related_memory: [warning:google-oauth-placeholders]
id: firebase-auth
type: functionality
title: Firebase authentication
updated: 2026-07-21
---

## In one sentence

Google, Apple, and email/password sign-in with AuthCubit state machine and RevenueCat identity sync.

## Current behavior

Implemented in source per files listed in front matter. Runtime behavior depends on Firebase and API configuration.

## Current state

**Status:** implemented. **Verification:** verified-from-source.

## Safe to change

Presentation and copy with localization.

## Use caution

Data writes and subscription checks.
