---
id: subscription-access-gating
type: functionality
title: Subscription access gating
area: subscription
summary: Paid access is determined from Superwall subscription status and RevenueCat CustomerInfo, not Firestore alone.
status: implemented
verification: verified-from-source
user_facing: false
trigger: Any check of isPaidSubscriber / AuthCubit paid state / onboarding completion.
files: [lib/core/subscription/subscription_service.dart, lib/core/auth/cubit/auth_cubit.dart, lib/core/superwall/superwall_purchase_controller.dart]
reads: []
writes: []
config: []
depends_on: [app-startup]
related_memory: [decision:revenuecat-not-firestore-gating, warning:android-superwall-purchase-controller]
created: 2026-07-21
updated: 2026-07-21
tags: []
---

## In one sentence

RevenueCat/Superwall decide paid access; Firestore stores mirrors only.

## Current behavior

`SubscriptionService.isPaidSubscriber` checks debug/reviewer bypasses, Superwall
status, then RevenueCat entitlements/subscriptions. `AuthCubit` emits paid/free
states. `SuperwallPurchaseController` maps store purchases through RevenueCat
and syncs status. Android early Superwall configure omits purchaseController —
treat Android sync as higher risk.

## Current state

Implemented with platform caveat; verified-from-source.
