---
id: learn-video-lessons
type: functionality
title: Learn video lessons
area: education
summary: Hardcoded Mux HLS lesson catalog with local subtitle assets and completion tracked in SharedPreferences.
status: implemented
verification: verified-from-source
user_facing: true
trigger: User opens Learn videos.
files: [lib/features/learn/presentation/cubit/learn_video_cubit.dart, lib/features/learn/presentation/screens/full_screen_video_player_screen.dart, lib/features/learn/presentation/screens/learn_video_list_screen.dart]
reads: []
writes: []
config: []
depends_on: [soft-paywalls-quotas]
related_memory: [discovery:quotas-disabled-ab-test]
created: 2026-07-21
updated: 2026-07-23
tags: []
---

## In one sentence

Lessons stream from Mux URLs; quota gating is currently disabled.

## Current behavior

`LearnVideoCubit` holds the lesson list. Player uses `video_player` with SRT
assets and Mux subtitle fallback. Soft paywall placement still referenced for
when quotas are re-enabled.

## Current state

Implemented; verified-from-source.
