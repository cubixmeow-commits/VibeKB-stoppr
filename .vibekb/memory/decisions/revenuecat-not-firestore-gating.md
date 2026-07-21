---
id: revenuecat-not-firestore-gating
type: decision
title: RevenueCat/Superwall gate access, not Firestore
summary: Paid access ignores Firestore subscription fields for granting entitlements.
status: active
verification: verified-from-source
functionality: [subscription-access-gating, startup-routing]
files: [lib/core/subscription/subscription_service.dart]
alternatives: [Trust Firestore subscriptionStatus]
updated: 2026-07-21
---

## Context

Subscription fields are written to `users/{uid}` for analytics/display.

## Decision

`isPaidSubscriber` consults Superwall and RevenueCat only (plus explicit
bypasses). Comment in source: Firebase data is no longer consulted for granting
access.

## Consequences

Tampering with Firestore subscription fields must not unlock premium. Promo
access that only sets Firestore fields may fail unless Superwall status is also
synced.
