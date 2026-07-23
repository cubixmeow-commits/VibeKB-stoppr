---
id: superwall-placement-placeholders
type: warning
title: Superwall placement IDs are placeholders
summary: Many registerPlacement calls still use INSERT_YOUR_* placement id strings.
severity: high
verification: verified-from-source
functionality: [main-paywall, soft-paywalls-quotas, notifications]
files: [lib/features/onboarding/presentation/screens/pre_paywall.dart, lib/features/app/presentation/screens/home_screen.dart]
updated: 2026-07-21
---

## What can go wrong

Paywalls may not show or may no-op depending on Superwall dashboard mapping.

## Evidence

Grep `INSERT_YOUR_` under `lib/`.

## Safe next action

Replace placement IDs in source or map them in Superwall; then re-verify
purchases on both platforms.
