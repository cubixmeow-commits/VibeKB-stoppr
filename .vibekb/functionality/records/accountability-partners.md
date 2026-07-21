---
id: accountability-partners
type: functionality
title: Accountability partners
area: community
summary: Partnerships and matching pool in Firestore; joining the pool requires an active subscription.
status: implemented
verification: verified-from-source
user_facing: true
trigger: User requests, accepts, or matches an accountability partner.
files: [lib/features/accountability/data/repositories/accountability_repository.dart, lib/core/accountability/accountability_service.dart, lib/core/accountability/accountability_widget_service.dart]
reads: []
writes: []
config: []
depends_on: [subscription-access-gating]
related_memory: []
created: 2026-07-21
updated: 2026-07-21
tags: []
---

## In one sentence

Accountability matching is subscription-gated and widget-synced.

## Current behavior

Repositories manage `accountability_partnerships` and `accountability_pool`.
`joinPool` checks `SubscriptionService`. Widget service pushes partner status to
home widgets.

## Current state

Implemented; verified-from-source.
