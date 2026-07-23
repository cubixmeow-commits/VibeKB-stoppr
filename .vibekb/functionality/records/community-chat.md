---
id: community-chat
type: functionality
title: Community chat
area: community
summary: "Realtime chat collections official_chat and official_chat_{lang}; Crisp provides separate support chat."
status: implemented
verification: verified-from-source
user_facing: true
trigger: User opens official or language chat rooms.
files: [lib/features/community/data/repositories/chat_repository.dart, lib/features/community/presentation/screens/language_chat_screen.dart, lib/core/chat/crisp_service.dart]
reads: []
writes: []
config: []
depends_on: [firebase-auth]
related_memory: []
created: 2026-07-21
updated: 2026-07-23
tags: []
---

## In one sentence

In-app chat is Firestore streams; Crisp is customer support chat.

## Current behavior

`ChatRepository` reads/writes chat collections, may ensure anonymous auth, and
tracks last-seen timestamps in SharedPreferences. Crisp uses `CRISP_WEBSITE_ID`.

## Current state

Implemented; verified-from-source.
