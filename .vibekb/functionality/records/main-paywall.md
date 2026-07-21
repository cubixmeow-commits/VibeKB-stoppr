---
area: subscription
summary: PrePaywallScreen presents Superwall placements for standard subscription purchase.
status: partial
verification: verified-from-source
user_facing: true
trigger: User completes onboarding or returns unpaid after onboarding.
files: [lib/features/onboarding/presentation/screens/pre_paywall.dart, lib/core/superwall/superwall_purchase_controller.dart]
reads: []
writes: [users]
config: [SUPERWALL_API_KEY, INSERT_YOUR_STANDARD_PAYWALL_PLACEMENT_ID_HERE]
depends_on: [onboarding-personalized-analysis, subscription-access-gating]
related_memory: [warning:superwall-placement-placeholders]
id: main-paywall
type: functionality
title: Main subscription paywall
updated: 2026-07-21
---

## In one sentence

The primary Superwall paywall blocks unpaid users after onboarding.

## Current behavior

`PrePaywallScreen` (~2600 lines) registers Superwall placements for gift steps, standard paywall, and X-tap dismiss variants. Placement IDs in source are placeholders (`INSERT_YOUR_*`).

## Step-by-step flow

1. User reaches end of onboarding funnel.
2. Screen registers Superwall placement.
3. On purchase → PostPurchaseHandler → Congratulations screens.
4. On dismiss → may show alternate placements.

## Failure cases

- Placeholder placement IDs will not show real paywalls without dashboard config.
- Anonymous user may be auto-created on purchase.

## Current state

**Status:** partial — UI and controller implemented; placement config external.

## Use caution

Payment flow touches RevenueCat, Superwall, Firebase, and Facebook events.
