---
id: system-request-flow
type: system
title: Request and navigation flow
summary: Imperative Navigator flows from main bootstrap; deep links inject target screens.
updated: 2026-07-21
---

## How control moves

Stoppr does not use a single declarative router for the whole app. `main.dart`
computes an initial widget tree, then screens push/replace via Flutter
`Navigator`. Deep links and widget URIs call into `_processDeepLink` or set
pending navigation flags.

Paid checks run at startup, onboarding completion, and feature gates via
`SubscriptionService` / `AuthCubit`.
