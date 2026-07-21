---
area: community
summary: Matchmaking pool, invite links, and partnership management for subscribed users.
status: implemented
verification: inferred-from-source
user_facing: true
trigger: User opens Accountability Partner from home.
files: [lib/core/accountability/accountability_service.dart, lib/features/accountability/presentation/screens/accountability_partner_screen.dart]
reads: [accountability_partnerships, accountability_pool, users]
writes: [accountability_partnerships, accountability_pool]
depends_on: [subscription-access-gating, home-dashboard]
related_memory: []
id: accountability-partners
type: functionality
title: Accountability partners
updated: 2026-07-21
---

## In one sentence

Matchmaking pool, invite links, and partnership management for subscribed users.

## Current behavior

Implemented in source per files listed in front matter. Runtime behavior depends on Firebase and API configuration.

## Current state

**Status:** implemented. **Verification:** inferred-from-source.

## Safe to change

Presentation and copy with localization.

## Use caution

Data writes and subscription checks.
