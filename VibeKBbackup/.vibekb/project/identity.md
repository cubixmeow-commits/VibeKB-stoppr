---
id: project-identity
type: project
title: Stoppr
summary: A Flutter mobile app that helps users reduce sugar consumption through onboarding, habit tracking, education, community support, and premium wellness tools.
one_liner: A sugar-reduction companion with streak tracking, panic interventions, nutrition tools, and subscription-gated premium features.
intended_users: People seeking to reduce sugar intake; the app uses addiction-recovery and behavioral-science framing in its copy.
primary_outcome: Sustained sugar-free streaks, daily engagement, and optional premium access to advanced tools.
stack_language: Dart / Flutter 3.x
stack_backend: Firebase (Auth, Firestore, Storage, Messaging, Crashlytics, Analytics)
stack_monetization: Superwall paywalls + RevenueCat purchases
stack_analytics: Mixpanel (primary), AppsFlyer, Facebook App Events
source_repository: https://github.com/cubixmeow-commits/VibeKB-stoppr
verification: verified-from-source
updated: 2026-07-21
---

## What the software is

Stoppr is a cross-platform Flutter application (iOS and Android) focused on
helping users break sugar habits. The app combines a long onboarding funnel,
Firebase-backed user data, local persistence via SharedPreferences, and a
subscription model gating premium features.

The app presents health and behavioral claims in its UI copy. This guide
documents **software behavior only** — it does not endorse medical claims.

> Verified from source: `lib/main.dart`, `pubspec.yaml`, `README.md`,
> `lib/features/onboarding/`, `lib/core/subscription/subscription_service.dart`.

## Who uses it

- New users complete onboarding (questionnaire, profile, goals, paywall).
- Authenticated users (Google, Apple, email) or anonymous/guest users.
- Subscribers access premium features; free users hit soft paywalls and quotas.

## Current scope

- Full onboarding funnel with resume support.
- Home hub with streak, check-in, panic button, and feature grid.
- Community forum, language chat rooms, accountability partners.
- Educational videos (8 lessons), local articles, recipes, nutrition tracking.
- Food scanning (sugar analysis via Groq vision API).
- Meditation, breathing, 28-day challenge, fasting, journaling.
- Push and local notifications with subscription-aware scheduling.
