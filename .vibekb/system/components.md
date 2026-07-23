---
id: system-components
type: system
title: Components
summary: "Major modules: main bootstrap, auth, onboarding, subscription, features, and native widgets."
updated: 2026-07-23
---

## Major components

| Component | Role | Primary path |
|-----------|------|--------------|
| Bootstrap | SDK init + first route | `lib/main.dart` |
| Auth | Firebase identity + AuthCubit | `lib/core/auth/` |
| Onboarding | Funnel + progress service | `lib/features/onboarding/` |
| Subscription | RevenueCat + Superwall | `lib/core/subscription/`, `lib/core/superwall/` |
| Home / panic / pledge | Daily loop | `lib/features/app/` |
| Community / accountability | Social | `lib/features/community/`, `lib/features/accountability/` |
| Nutrition / recipes | Food tools | `lib/features/nutrition/`, `lib/features/recipes/` |
| Learn | Videos + articles | `lib/features/learn/` |
| Widgets | iOS/Android home widgets | `ios/StreakWidget/`, Android providers |
| Config | Env accessors | `lib/core/config/env_config.dart` |
