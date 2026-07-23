---
id: startup-routing
type: functionality
title: Startup routing
area: app-core
summary: Chooses WelcomeVideo, onboarding resume, PrePaywall, or MainScaffold from auth, onboarding completion, and paid status.
status: implemented
verification: verified-from-source
user_facing: true
trigger: After SDKs initialize, MyApp resolves the first screen.
files: [lib/main.dart, lib/features/onboarding/data/services/onboarding_progress_service.dart]
reads: []
writes: []
config: []
depends_on: [app-startup, subscription-access-gating]
related_memory: [decision:revenuecat-not-firestore-gating]
created: 2026-07-21
updated: 2026-07-23
tags: []
---

## In one sentence

`_checkOnboardingProgress()` picks the first real screen after splash loading.

## Current behavior

While `_isLoading` is true, a spinner scaffold shows. Then routing considers
local `onboarding_completed`, Firebase user restoration, Firestore
`onboardingCompleted`, and `SubscriptionService.isPaidSubscriber`. Paid users
with incomplete onboarding are forced into `MainScaffold` and marked complete.
Unpaid completed users land on `PrePaywallScreen`. Incomplete unpaid users
resume via `OnboardingProgressService`.

## Failure cases

- Auth restoration timing (waits up to ~3s) can still race on slow devices.
- Debug/TestFlight bypasses can skip paywall paths.

## Current state

Implemented; verified-from-source.
