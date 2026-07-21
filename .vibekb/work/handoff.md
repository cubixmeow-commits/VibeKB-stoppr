---
id: handoff
type: work
title: VibeKB integration handoff
summary: Start with functionality index and warnings; re-verify paywall and subscription flows before changing status claims.
verification_state: verified-from-source
updated: 2026-07-21
---

## What was inspected

- `lib/main.dart` — bootstrap, routing, deep links, Superwall delegate
- `lib/features/onboarding/` — full funnel, OnboardingProgressService
- `lib/core/subscription/` — SubscriptionService, FeatureQuotaService, SuperwallPurchaseController
- `lib/features/app/presentation/screens/home_screen.dart` — home hub
- `lib/features/community/`, `lib/features/nutrition/`, `lib/features/recipes/`
- `lib/core/streak/`, `lib/core/notifications/`, `lib/core/analytics/`
- `firestore.rules`, `pubspec.yaml`, `README.md`

## What was modeled

- 30 functionality records in 10 groups
- 6 system documents (mental model through deployment)
- 31 important files in `important-files.json`
- 2 decisions, 2 constraints, 1 assumption, 4 warnings, 4 discoveries, 1 change

## What remains uncertain

- Whether Superwall paywalls work with configured dashboard (placement IDs are placeholders in source)
- Whether accountability pool matching Cloud Functions are deployed
- Actual Firebase behavior in production (only rules and client code inspected)
- Whether disabled A/B quota bypasses are intentional in current release branch

## What could not be run

- Flutter app (no device/simulator in this environment)
- `flutter test` (no tests exist)
- Firebase or payment sandbox transactions
- Groq/OpenAI API calls

## External services requiring configuration

Firebase, RevenueCat, Superwall, Mixpanel, AppsFlyer, Groq, OpenAI, Edamam, Spoonacular, Google OAuth, Crisp — all via `.env`.

## Areas deserving direct testing

1. Main paywall purchase flow end-to-end
2. Subscription gating on accountability partners and learn videos
3. Food scan with real Groq API key
4. Push notification delivery and tap → Superwall placement
5. Deep links (payment success, share invite, panic)

## Records most likely needing revision

- `main-paywall` — when placement IDs are configured
- `soft-paywalls-quotas` — when A/B test concludes
- `accountability-partners` — after Cloud Function verification
- `daily-check-in-pledge` — if check-in on load re-enabled

## How to update VibeKB after code changes

1. Identify affected functionality records in `.vibekb/functionality/records/`.
2. Update status, verification, and narrative from source evidence.
3. Add memory records (warning, discovery, change) for non-obvious findings.
4. Update `work/current.md` during active AI sessions.
5. Run `python3 .vibekb/tools/generate_docs.py`.
6. Confirm Reference view shows zero validation errors.
