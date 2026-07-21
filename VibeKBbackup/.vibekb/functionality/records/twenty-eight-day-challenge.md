---
area: wellness-tools
summary: ChallengeService tracks daily tasks with local and Firestore sync; includes panic button footer.
status: implemented
verification: verified-from-source
user_facing: true
trigger: User opens 28-Day Challenge from home.
files: [lib/core/challenge/challenge_service.dart, lib/features/app/presentation/screens/challenge_28_days_screen.dart]
reads: [shared_preferences, users]
writes: [shared_preferences, users]
depends_on: [home-dashboard, soft-paywalls-quotas]
related_memory: []
id: twenty-eight-day-challenge
type: functionality
title: 28-day challenge
updated: 2026-07-21
---

## In one sentence

ChallengeService tracks daily tasks with local and Firestore sync; includes panic button footer.

## Current behavior

Implemented in source per files listed in front matter. Runtime behavior depends on Firebase and API configuration.

## Current state

**Status:** implemented. **Verification:** verified-from-source.

## Safe to change

Presentation and copy with localization.

## Use caution

Data writes and subscription checks.
