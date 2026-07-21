---
area: authentication
summary: Anonymous Firebase users created during onboarding, promos, and some feature flows with 90-day TTL.
status: implemented
verification: verified-from-source
user_facing: true
trigger: User skips auth, accepts promo, or feature requires uid without sign-in.
files: [lib/core/repositories/user_repository.dart, lib/features/onboarding/presentation/screens/profile_info_screen.dart]
reads: [users]
writes: [users]
depends_on: [firebase-auth]
related_memory: []
id: guest-anonymous-access
type: functionality
title: Guest and anonymous access
updated: 2026-07-21
---

## In one sentence

Anonymous Firebase users created during onboarding, promos, and some feature flows with 90-day TTL.

## Current behavior

Implemented in source per files listed in front matter. Runtime behavior depends on Firebase and API configuration.

## Current state

**Status:** implemented. **Verification:** verified-from-source.

## Safe to change

Presentation and copy with localization.

## Use caution

Data writes and subscription checks.
