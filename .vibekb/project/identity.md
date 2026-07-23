---
id: project-identity
type: project
title: Stoppr
summary: A Flutter mobile app that helps users reduce sugar consumption through onboarding, habit tracking, education, community, and subscription-gated wellness tools.
one_liner: A sugar-reduction companion with streaks, panic interventions, nutrition tools, and premium gating.
intended_users: People seeking to reduce sugar intake; UI copy uses addiction-recovery framing.
primary_outcome: Sustained engagement around sugar-free streaks and daily habits, with optional premium access.
stack_language: Dart / Flutter 3.x (SDK ^3.7.0)
stack_backend: Firebase Auth, Firestore, Storage, Messaging, Crashlytics, Analytics
stack_monetization: Superwall paywalls + RevenueCat purchases
stack_analytics: Mixpanel, AppsFlyer, Facebook App Events
source_repository: "https://github.com/cubixmeow-commits/VibeKB-stoppr"
verification: verified-from-source
updated: 2026-07-23
---

## What the software is

Stoppr (package name `stoppr`, public name **Stoppr**) is a cross-platform
Flutter application for iOS and Android. Version `7.4.2+1` is declared in
`pubspec.yaml`. It combines a long onboarding funnel, Firebase-backed user
data, SharedPreferences local state, native home-screen widgets, and a
subscription model that gates premium access via Superwall and RevenueCat.

This guide documents **software behavior only**. It does not endorse medical
or health claims that appear in app copy.

## Who uses it

- New users walking a multi-step onboarding funnel ending at a paywall.
- Authenticated users (Google, Apple, email) or anonymous/guest users.
- Paid subscribers and free/trial users under different access rules.

## Alternate names

Only names supported by the repository itself: **Stoppr**, package `stoppr`,
and related bundle identifiers under `com.stoppr` / `com.app.stoppr` in
platform manifests. Do not invent alternate product names.

## Evidence

Traced in `pubspec.yaml`, `lib/main.dart`, `lib/core/config/env_config.dart`,
and feature directories under `lib/features/`.
