---
id: app-overview
type: diagram
title: Stoppr application overview
summary: How the Flutter client relates to Firebase, purchases, local state, and external AI/food APIs.
diagram_type: application-overview
group: whole-app
svg: app-overview.svg
topology: app-overview.json
functionality: [app-startup, firebase-auth, subscription-access-gating, sugar-streak-tracking, chatbot-assistant]
files: [lib/main.dart]
data: [Firestore, SharedPreferences, HomeWidget]
warnings: [placeholder-api-keys]
diagrams: [onboarding-auth-flow, subscription-paywall-flow]
status: implemented
verification: verified-from-source
provenance: "Traced lib/main.dart, auth, subscription, streak, and env_config against source at analysis commit."
last_verified: 2026-07-21
uncertainty: "Firestore mirror field completeness after purchase is inferred; store dashboard config not verified."
created: 2026-07-21
updated: 2026-07-23
---

## What am I looking at?

The Stoppr mobile client sits at the center. It depends on Firebase for identity
and documents, on RevenueCat/Superwall for entitlements, on device-local state
for streaks/onboarding/widgets, and on external AI/food APIs for nutrition and
chat features.

## Why it matters

It is the fastest honest map of what the software is doing before reading
individual functionality records.

## What is uncertain

Purchase→Firestore mirroring is inferred-from-source (dashed). External API
runtime success depends on private env values not present in git.
