---
id: project-intent
type: project
title: Project intent
summary: Stoppr exists to guide users through sugar reduction with habit tracking, education, community, and monetized premium tools — not as a medical device.
verification: verified-from-source
updated: 2026-07-21
---

## Why it exists

Stoppr packages sugar-reduction support into a mobile experience: onboarding
that collects goals and symptoms, daily streak motivation, crisis "panic"
interventions, educational content, and social accountability.

## What it must not become (product boundaries visible in code)

- Not a clinician-facing tool — no provider portal or medical records integration.
- Not offline-first — Firebase and network APIs are required for most flows.
- Not web-first — `firebase_options.dart` throws for web/desktop targets.
- Subscription gating is central — many features check `SubscriptionService`.

## Design philosophy visible in source

- **Imperative navigation** — no GoRouter; hundreds of `Navigator.push` calls.
- **Service singletons** — cross-cutting logic (streak, notifications, subscription).
- **Cubits for feature screens** — community, learn, recipes use Bloc/Cubit.
- **Env-driven config** — API keys via `.env` and `EnvConfig`, not hardcoded.
