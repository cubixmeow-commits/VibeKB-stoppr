---
id: request-flow
type: system
title: Application lifecycle
summary: Every app launch runs main() initialization, resolves locale and auth, then routes to welcome video, onboarding resume, paywall, or MainScaffold home.
verification: verified-from-source
updated: 2026-07-21
---

## Cold start sequence

1. `main()` in `lib/main.dart` runs `WidgetsFlutterBinding.ensureInitialized()`.
2. Load `.env`, configure RevenueCat early, enumerate cameras.
3. `Firebase.initializeApp()`, Crashlytics (prod), NotificationService init.
4. Crisp, QuickActions, SharedPreferences, AuthService, Mixpanel, AppsFlyer.
5. FCM background handler registration, Superwall early init (Android).
6. `runApp(MyApp)` with `AuthCubit` provider.

## `_MyAppState` startup routing

`_determineStartScreen()` evaluates:

- Is onboarding complete? (`OnboardingProgressService`)
- Is user authenticated?
- Is user a paid subscriber? (`SubscriptionService`)
- Last onboarding screen for resume (`lastOnboardingScreen`)

Possible destinations:

- `WelcomeVideoScreen` → onboarding entry
- Specific onboarding screen (resume)
- `PrePaywallScreen` (unpaid, onboarding done)
- `MainScaffold` (paid or bypass)

## In-session navigation

- Tab changes within `MainScaffold` (indices 0–4).
- Feature launches from `HomeScreen` via `Navigator.push`.
- Deep links handled in `_processDeepLink()` (payment success, panic, pledge, share invites).

## Background / resume

- FCM messages may trigger Superwall placements on tap.
- `AppOpenStreakService` tracks consecutive daily opens.
- Subscription refresh on payment success deep link.
