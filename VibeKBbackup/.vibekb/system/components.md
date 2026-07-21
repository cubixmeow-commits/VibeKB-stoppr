---
id: components
type: system
title: Major components
summary: Stoppr is organized into core services, feature modules under lib/features/, and presentation screens with Cubits for stateful UI.
verification: verified-from-source
updated: 2026-07-21
---

## Core (`lib/core/`)

| Component | Role |
|-----------|------|
| `AuthService` / `AuthCubit` | Firebase auth state machine |
| `SubscriptionService` | Paid access checks via RevenueCat/Superwall |
| `SuperwallPurchaseController` | Bridges Superwall to RevenueCat |
| `StreakService` | Sugar-free streak timer + widget sync |
| `NotificationService` | FCM + local notifications |
| `MixpanelService` | Event tracking |
| `UserRepository` | Firestore user CRUD |
| `EnvConfig` | Typed environment variable accessors |

## Feature modules (`lib/features/`)

| Module | Key screens / services |
|--------|------------------------|
| `onboarding/` | 30+ screens, `OnboardingProgressService` |
| `app/` | `HomeScreen`, `MainScaffold`, panic, food scan, meditation |
| `community/` | `CommunityRepository`, `CommunityCubit` |
| `accountability/` | `AccountabilityService`, partner matching |
| `learn/` | `LearnVideoCubit`, `ArticleService` |
| `nutrition/` | `NutritionRepository`, calorie tracker |
| `recipes/` | `RecipeRepository`, Edamam/Spoonacular |
| `auth/` | `EmailAuthScreen` |

## State management pattern

- **Cubit + Freezed** for feature screens (community, learn, recipes).
- **Singleton services** for cross-cutting domain logic.
- **BlocProvider** at app root for `AuthCubit`; `RepositoryProvider` for community.

## Third-party UI

- `superwallkit_flutter` — paywall presentation.
- `purchases_flutter` — RevenueCat SDK.
- `flutter_local_notifications` — scheduled reminders.
