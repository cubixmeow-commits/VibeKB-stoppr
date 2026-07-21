---
id: mental-model
type: system
title: Mental model
summary: Stoppr is a Flutter shell around Firebase user state, local preferences, and subscription-gated feature modules reached through imperative navigation.
verification: verified-from-source
updated: 2026-07-21
---

## The simplest mental model

Think of Stoppr as three layers:

1. **Bootstrap layer** (`main.dart`) — initializes Firebase, purchases, analytics,
   notifications, then decides which screen to show first.
2. **Session layer** — `AuthCubit` + `SubscriptionService` determine whether the
   user sees onboarding, paywall, or the home hub.
3. **Feature modules** — self-contained areas (community, nutrition, learn) that
   read/write Firestore and SharedPreferences through service singletons.

## User journey axes

| Axis | Mechanism |
|------|-----------|
| New vs returning | `OnboardingProgressService` + Firestore `lastOnboardingScreen` |
| Free vs paid | `SubscriptionService.isPaidSubscriber()` |
| Authenticated vs guest | Firebase anonymous auth in several flows |

## Navigation model

There is no central route table. `MainScaffold` hosts five tabs; everything
else is reached via `Navigator.push` / `pushReplacement` from `HomeScreen`
or onboarding screens.

## Data model

- **Cloud**: Firestore `users/{uid}` document + subcollections.
- **Local**: SharedPreferences for streak, onboarding progress, quotas, settings.
- **Purchases**: RevenueCat + Superwall, not Firestore subscription fields.
