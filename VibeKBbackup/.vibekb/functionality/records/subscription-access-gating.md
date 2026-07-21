---
area: subscription
summary: SubscriptionService.isPaidSubscriber checks RevenueCat, Superwall, debug bypasses, and reviewer emails.
status: implemented
verification: verified-from-source
user_facing: false
trigger: Any premium feature or routing decision checks subscription.
files: [lib/core/subscription/subscription_service.dart, lib/core/repositories/user_repository.dart]
reads: []
writes: []
depends_on: [app-startup]
related_memory: [decision:revenuecat-not-firestore-gating]
id: subscription-access-gating
type: functionality
title: Subscription access gating
updated: 2026-07-21
---

## In one sentence

SubscriptionService.isPaidSubscriber checks RevenueCat, Superwall, debug bypasses, and reviewer emails.

## Current behavior

Implemented in source per files listed in front matter. Runtime behavior depends on Firebase and API configuration.

## Current state

**Status:** implemented. **Verification:** verified-from-source.

## Safe to change

Presentation and copy with localization.

## Use caution

Data writes and subscription checks.
