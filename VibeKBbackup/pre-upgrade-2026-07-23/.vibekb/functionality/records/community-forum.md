---
id: community-forum
type: functionality
title: Community forum
area: community
summary: Firestore-backed posts and nested comments with blocked-user filtering.
status: implemented
verification: verified-from-source
user_facing: true
trigger: User opens Community and browses or posts.
files: [lib/features/community/data/repositories/community_repository.dart, lib/features/community/presentation/screens/community_screen.dart]
reads: []
writes: []
config: []
depends_on: [firebase-auth]
related_memory: []
created: 2026-07-21
updated: 2026-07-21
tags: []
---

## In one sentence

Forum content lives in `community_posts` (+ `comments` subcollection).

## Current behavior

Create/list/like/comment flows via `CommunityRepository`. Blocked users filtered
from views.

## Current state

Implemented; verified-from-source.
