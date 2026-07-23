---
id: guest-anonymous-access
type: functionality
title: Guest anonymous access
area: authentication
summary: Anonymous Firebase accounts are created from several entry points when no user exists, with TTL fields for guest profiles.
status: implemented
verification: verified-from-source
user_facing: true
trigger: User skips auth during onboarding or a service needs a Firebase uid.
files: [lib/features/onboarding/presentation/screens/profile_info_screen.dart, lib/features/nutrition/data/repositories/nutrition_repository.dart, lib/core/usage/feature_quota_service.dart]
reads: []
writes: []
config: []
depends_on: [firebase-auth]
related_memory: [discovery:anonymous-auth-spread]
created: 2026-07-21
updated: 2026-07-23
tags: []
---

## In one sentence

`signInAnonymously()` is used from profile completion and some repositories.

## Current behavior

`ProfileInfoScreen` creates an anonymous user if needed before writing profile
fields. Nutrition and quota services can also ensure auth. Anonymous TTL is
refreshed via `UserRepository`.

## Current state

Implemented but multi-entry (not centralized). Verified-from-source.
