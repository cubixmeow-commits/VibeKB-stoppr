---
area: community
summary: Users browse, create, upvote posts and comment via CommunityRepository and Cubits.
status: implemented
verification: verified-from-source
user_facing: true
trigger: User opens Community tab.
files: [lib/features/community/data/repositories/community_repository.dart, lib/features/community/presentation/screens/community_screen.dart]
reads: [community_posts, users]
writes: [community_posts, users]
depends_on: [main-navigation, firebase-auth]
related_memory: []
id: community-forum
type: functionality
title: Community forum posts
updated: 2026-07-21
---

## In one sentence

Users browse, create, upvote posts and comment via CommunityRepository and Cubits.

## Current behavior

Implemented in source per files listed in front matter. Runtime behavior depends on Firebase and API configuration.

## Current state

**Status:** implemented. **Verification:** verified-from-source.

## Safe to change

Presentation and copy with localization.

## Use caution

Data writes and subscription checks.
