---
area: community
summary: Per-language chat rooms (en, es, de, zh, ru, fr, sk, cs, it, pl) with real-time messages.
status: implemented
verification: verified-from-source
user_facing: true
trigger: User selects a language chat room from Community screen.
files: [lib/features/community/presentation/screens/language_chat_screen.dart, lib/features/community/presentation/state/chat_cubit.dart]
reads: [official_chat]
writes: [official_chat]
depends_on: [community-forum]
related_memory: []
id: community-chat
type: functionality
title: Official and language chat
updated: 2026-07-21
---

## In one sentence

Per-language chat rooms (en, es, de, zh, ru, fr, sk, cs, it, pl) with real-time messages.

## Current behavior

Implemented in source per files listed in front matter. Runtime behavior depends on Firebase and API configuration.

## Current state

**Status:** implemented. **Verification:** verified-from-source.

## Safe to change

Presentation and copy with localization.

## Use caution

Data writes and subscription checks.
