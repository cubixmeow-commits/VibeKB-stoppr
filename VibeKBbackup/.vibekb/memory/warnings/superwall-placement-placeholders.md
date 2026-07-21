---
id: superwall-placement-placeholders
type: warning
title: Superwall placement IDs are placeholders in source
summary: Paywall registration uses INSERT_YOUR_* placeholder strings requiring Superwall dashboard configuration.
severity: high
verification: verified-from-source
functionality: [main-paywall, soft-paywalls-quotas]
files: [lib/features/onboarding/presentation/screens/pre_paywall.dart, lib/features/app/presentation/screens/home_screen.dart]
updated: 2026-07-21
---

## What can go wrong

Without replacing placeholders in code or Superwall dashboard, paywalls may not appear or may fail silently in production.

## Affected areas

PrePaywallScreen, home_screen soft paywalls, food scan, chatbot, learn videos, quick actions, redownload feedback.

## Evidence

Grep for `INSERT_YOUR_` across `lib/`.
