---
id: project-current-state
type: project
title: Current state
summary: Stoppr v7.4.2+1 is a feature-rich Flutter app with implemented onboarding, home hub, community, nutrition, and subscription infrastructure — but placeholder Superwall placement IDs and no automated tests.
verification: verified-from-source
updated: 2026-07-21
---

## What works in source (not runtime-verified here)

| Area | State | Evidence |
|------|-------|----------|
| App bootstrap | Implemented | `lib/main.dart` — Firebase, RevenueCat, Mixpanel, notifications |
| Onboarding | Implemented | 30+ screens, `OnboardingProgressService` |
| Auth | Implemented | Google, Apple, email, anonymous users |
| Paywall UI | Partial | Superwall integrated; placement IDs are placeholders |
| Home hub | Implemented | `home_screen.dart` (~3800 lines) |
| Streak / check-in | Implemented | `StreakService`, `DailyCheckInWidget`, `PledgeService` |
| Panic flow | Implemented | `PanicFlowManager`, 18+ intervention screens |
| Community | Implemented | Firestore `community_posts`, chat rooms |
| Nutrition | Implemented | Firestore `food_logs`, Edamam API |
| Food scan | Implemented | Groq vision API; quota checks disabled for A/B |
| Notifications | Implemented | `NotificationService` (~3900 lines) |
| Tests | Missing | No `test/` directory or `*_test.dart` files |

## Known gaps

- Superwall placement strings like `INSERT_YOUR_STANDARD_PAYWALL_PLACEMENT_ID_HERE`.
- iOS home widget app group: `group.YOUR_BUNDLE_ID.shared`.
- Feature quotas commented out in food scan and chatbot for A/B testing.
- Fasting uses local SharedPreferences despite Firestore rules for `fasts`.
