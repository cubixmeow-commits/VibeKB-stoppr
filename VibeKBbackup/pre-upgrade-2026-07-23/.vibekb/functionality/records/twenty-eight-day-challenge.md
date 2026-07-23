---
id: twenty-eight-day-challenge
type: functionality
title: Twenty-eight day challenge
area: wellness-tools
summary: "Day/task progression stored in SharedPreferences and users/{uid}/challenges with varied task types."
status: implemented
verification: verified-from-source
user_facing: true
trigger: User starts or continues the 28-day challenge.
files: [lib/core/challenge/challenge_service.dart, lib/features/app/presentation/screens/challenge_28_days_screen.dart]
reads: []
writes: []
config: []
depends_on: [home-dashboard]
related_memory: []
created: 2026-07-21
updated: 2026-07-21
tags: []
---

## In one sentence

ChallengeService distributes daily tasks across wellness features.

## Current behavior

Tracks day status locally and in Firestore challenge/task docs. Task types
include journal, breathing, pledge, meditation, articles, food scan, chatbot,
community, and more.

## Current state

Implemented; verified-from-source.
