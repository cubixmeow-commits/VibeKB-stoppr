---
id: risk-uncertainty-map
type: diagram
title: Risk and uncertainty map
summary: Verified configuration landmines that can break auth, paywalls, widgets, or quota soft-gates.
diagram_type: risk-and-uncertainty-map
group: uncertainty
svg: risk-uncertainty-map.svg
topology: risk-uncertainty-map.json
functionality: [main-paywall, subscription-access-gating, firebase-auth, home-widgets, soft-paywalls-quotas]
files: [lib/main.dart]
data: []
warnings: [superwall-placement-placeholders, android-superwall-purchase-controller, google-oauth-placeholders, widget-app-group-placeholder, env-file-mismatch]
diagrams: [subscription-paywall-flow]
status: implemented
verification: verified-from-source
provenance: "Each landmine grepped and file-traced in source."
last_verified: 2026-07-21
uncertainty: "Runtime severity depends on private env and dashboard config not in git."
created: 2026-07-21
updated: 2026-07-21
---

## What am I looking at?

The active configuration landmines an agent should check before changing
monetization, auth, or widgets.

## Why it matters

These are the warnings most likely to waste a vibe coder's time if ignored.

## What is uncertain

How badly each fails in production depends on secrets and Superwall dashboard
state outside this repository.
