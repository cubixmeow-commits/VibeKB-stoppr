---
area: app-core
summary: Decides whether to show welcome video, resume onboarding, paywall, or home based on progress and subscription.
status: implemented
verification: verified-from-source
user_facing: true
trigger: App finishes initialization in _MyAppState.
files: [lib/main.dart, lib/features/onboarding/data/services/onboarding_progress_service.dart]
reads: [shared_preferences, users]
writes: []
depends_on: [app-startup, subscription-access-gating]
related_memory: []
id: startup-routing
type: functionality
title: Startup screen routing
updated: 2026-07-21
---

## In one sentence

After init, the app picks welcome, resume, paywall, or home.

## Current behavior

`_determineStartScreen()` and `_getTargetScreenFromProgress()` in `main.dart` read onboarding completion, auth state, subscription status, and `lastOnboardingScreen` to choose the initial widget.

## Step-by-step flow

1. Show loading spinner while determining target.
2. If onboarding incomplete → resume at saved screen or welcome video.
3. If complete but unpaid → PrePaywallScreen.
4. If paid or bypass → MainScaffold.

## Failure cases

- Subscription check timeout falls back per surrounding try/catch.
- Missing progress defaults to onboarding start.

## Safe to change

Loading UI, transition animations.

## Use caution

Routing logic affects every user entry path.
