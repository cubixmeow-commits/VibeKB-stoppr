---
id: revenuecat-not-firestore-gating
type: decision
title: Subscription gating via RevenueCat not Firestore
summary: isPaidSubscriber checks RevenueCat and Superwall; Firebase subscription fields are analytics-only.
status: active
verification: verified-from-source
functionality: [subscription-access-gating, main-paywall]
files: [lib/core/subscription/subscription_service.dart]
updated: 2026-07-21
---

## Context

User documents store subscription metadata in Firestore for analytics.

## Decision

`SubscriptionService.isPaidSubscriber()` never reads Firestore subscription fields for gating. It checks debug mode, TestFlight, reviewer emails, Superwall status, then RevenueCat entitlements.

## Reason

Purchase truth should come from the payment SDK, not potentially stale Firestore copies.

## Consequences

Firestore subscription fields can drift from actual entitlement state.
Premium access works offline if RevenueCat cache is warm.
