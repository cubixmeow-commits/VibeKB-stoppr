---
area: education
summary: Eight hardcoded Mux HLS lessons with multilingual subtitles and completion tracking.
status: implemented
verification: verified-from-source
user_facing: true
trigger: User opens Learn tab.
files: [lib/features/learn/presentation/cubit/learn_video_cubit.dart, lib/features/learn/presentation/screens/learn_video_list_screen.dart]
reads: [shared_preferences]
writes: [shared_preferences]
depends_on: [main-navigation, soft-paywalls-quotas]
related_memory: []
id: learn-video-lessons
type: functionality
title: Video lesson library
updated: 2026-07-21
---

## In one sentence

Eight hardcoded Mux HLS lessons with multilingual subtitles and completion tracking.

## Current behavior

Implemented in source per files listed in front matter. Runtime behavior depends on Firebase and API configuration.

## Current state

**Status:** implemented. **Verification:** verified-from-source.

## Safe to change

Presentation and copy with localization.

## Use caution

Data writes and subscription checks.
