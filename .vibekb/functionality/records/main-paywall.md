---
id: main-paywall
type: functionality
title: Main paywall
area: subscription
summary: PrePaywall registers Superwall placements and handles purchase success, but placement IDs are mostly INSERT_YOUR_* placeholders.
status: partial
verification: verified-from-source
user_facing: true
trigger: PrePaywallScreen after onboarding or unpaid return visits.
files: [lib/features/onboarding/presentation/screens/pre_paywall.dart, lib/core/subscription/post_purchase_handler.dart, lib/core/superwall/superwall_purchase_controller.dart]
reads: []
writes: []
config: []
depends_on: [subscription-access-gating]
related_memory: [warning:superwall-placement-placeholders]
created: 2026-07-21
updated: 2026-07-23
tags: []
---

## In one sentence

The main commercial gate is Superwall-driven from `PrePaywallScreen`.

## Current behavior

Checks subscription, restore purchases, registers gift/standard placements, and
on success runs `PostPurchaseHandler` (Firestore update, streak init,
congratulations navigation). Existing paid users skip to `MainScaffold`.

## Current state

Partial — code path implemented; dashboard placement IDs largely placeholders.
Verified-from-source.
