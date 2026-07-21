---
id: android-superwall-purchase-controller
type: warning
title: Android Superwall purchase controller wiring is incomplete
summary: Android early Superwall.configure does not pass SuperwallPurchaseController; later path returns early on Android.
severity: high
verification: verified-from-source
functionality: [subscription-access-gating, main-paywall]
files: [lib/main.dart, lib/core/superwall/superwall_purchase_controller.dart]
updated: 2026-07-21
---

## What can go wrong

Android purchases or Superwall status sync may diverge from iOS behavior.

## Safe next action

Trace and align Android configure + `configureAndSyncSubscriptionStatus` with
the iOS path; verify with a sandbox purchase.
