---
id: project-current-state
type: project
title: Current state
summary: A large Flutter client with full onboarding, auth, subscription gating, home wellness loops, community, nutrition AI tools, widgets, and several high-severity configuration placeholders.
verification: verified-from-source
updated: 2026-07-23
---

## What it currently does

Implemented surfaces (source-traced at commit `f01661b`):

- Cold-start SDK bootstrap and auth-aware routing in `lib/main.dart`.
- Google / Apple / email / anonymous authentication.
- Long onboarding with resume via `OnboardingProgressService`.
- Superwall + RevenueCat subscription gating; Firestore does not grant access.
- Home dashboard with streak, pledge/check-in, panic, challenge entry points.
- Community posts/comments, language chat rooms, accountability partners.
- Learn videos (Mux HLS) and local article assets with progress.
- Food scan / Rate My Plate / recipes via Groq, OpenAI, Edamam, Spoonacular.
- Melinda chatbot with daily API rate limit.
- Fasting tracker (local SharedPreferences).
- Local + push notifications; Mixpanel / AppsFlyer analytics.
- iOS WidgetKit and Android AppWidget providers bridged via `home_widget`.

Navigation is imperative (`Navigator` / named routes), not GoRouter.

## Partial / risky

- Most Superwall `registerPlacement` calls still use `INSERT_YOUR_*` IDs.
- Feature quota soft-paywalls exist but `QUOTA_SYSTEM_ENABLED = false`.
- Android Superwall configure path does not attach `SuperwallPurchaseController`
  the same way as iOS.
- Widget app group id is still `group.YOUR_BUNDLE_ID.shared`.
- Google OAuth fallbacks are placeholder client IDs when env is missing.
- Only `.env.local` is present in the tree; `main()` loads `.env`.
- Relapse reason/help chips are largely UI-local (not fully persisted).
- Community blocked-user filtering is incomplete on the Cubit stream path.
- `stoppr://accountability` is emitted by widgets but not handled in Dart
  startup routing.

## Not verified at runtime

Store products, Superwall dashboard campaigns, and end-to-end purchase flows
were not executed in this analysis.
