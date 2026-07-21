---
id: project-intent
type: project
title: Intent
summary: Help users reduce sugar habits with daily engagement loops and optional premium tools, without turning the app into a generic health tracker.
verification: inferred-from-source
updated: 2026-07-21
---

## Why it exists

The product centers on sugar-habit reduction: onboarding personalization,
streaks, pledges, panic flows, education, community accountability, and
nutrition assistance. Monetization is subscription-based.

## What it must not become (from constraints in source)

- A documentation site or admin console — it is a mobile client.
- Dependent on Firestore alone for entitlement — access is RevenueCat/Superwall
  first (`SubscriptionService` comment and logic).
- A place that ships real secrets in the public repo — env-driven config with
  `.local` templates is the pattern.

## Uncertainty

Product roadmap beyond what the code implements is `not-verified`. Intent above
is inferred from implemented surfaces and README framing, not from a separate
product brief in-repo.
