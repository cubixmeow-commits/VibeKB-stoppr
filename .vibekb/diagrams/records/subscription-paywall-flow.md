---
id: subscription-paywall-flow
type: diagram
title: Subscription and paywall flow
summary: Superwall UI to purchase controller to RevenueCat to access gate, with post-purchase handling.
diagram_type: feature-access
group: product-flows
svg: subscription-paywall-flow.svg
topology: subscription-paywall-flow.json
functionality: [main-paywall, subscription-access-gating, soft-paywalls-quotas]
files: [lib/core/subscription/subscription_service.dart]
data: [RevenueCat CustomerInfo, Firestore subscription mirrors]
warnings: [superwall-placement-placeholders, android-superwall-purchase-controller]
diagrams: [app-overview, onboarding-auth-flow]
status: partial
verification: inferred-from-source
provenance: "Classes traced; Android configure asymmetry and placement placeholders labeled."
last_verified: 2026-07-21
uncertainty: "Store products and Superwall dashboard campaigns not executed."
created: 2026-07-21
updated: 2026-07-21
---

## What am I looking at?

How money and entitlements move: UI → controller → RevenueCat → access checks,
plus post-purchase handling.

## Why it matters

This is the highest commercial-risk path and contains known placeholder
placements and Android wiring uncertainty.

## What is uncertain

Dashed edges: Android purchaseController attachment and post-purchase
orchestration across files.
