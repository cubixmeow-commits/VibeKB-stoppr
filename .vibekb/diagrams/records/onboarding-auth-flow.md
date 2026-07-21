---
id: onboarding-auth-flow
type: diagram
title: Onboarding and authentication flow
summary: Welcome to auth to questionnaire/profile to pre-paywall to MainScaffold.
diagram_type: authentication-flow
group: product-flows
svg: onboarding-auth-flow.svg
topology: onboarding-auth-flow.json
functionality: [onboarding-intro, firebase-auth, onboarding-questionnaire, main-paywall, main-navigation]
files: [lib/features/onboarding/presentation/screens/pre_paywall.dart]
data: [SharedPreferences onboarding keys, users/onboarding]
warnings: [google-oauth-placeholders]
diagrams: [app-overview, subscription-paywall-flow]
status: implemented
verification: inferred-from-source
provenance: "Screens and AuthService traced; multi-file Navigator order partially inferred."
last_verified: 2026-07-21
uncertainty: "Resume branches and skip paths create alternate orders not fully drawn."
created: 2026-07-21
updated: 2026-07-21
---

## What am I looking at?

The happy-path onboarding sequence ending at Superwall, then the main app shell.

## Why it matters

Most new-user understanding starts here; auth placeholders and paywall IDs are
the highest-risk config along this path.

## What is uncertain

Dashed edge: questionnaire ordering after auth/skip is inferred from navigation
calls across files.
