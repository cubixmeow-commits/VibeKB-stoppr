---
id: project-constraints
type: project
title: Constraints
summary: Stoppr depends on Firebase, third-party APIs, and env-configured keys; mobile-only; subscription state from RevenueCat/Superwall not Firestore.
verification: verified-from-source
updated: 2026-07-21
---

## Platform constraints

- **iOS and Android only** — desktop/web targets unsupported in `firebase_options.dart`.
- **Flutter 3.x** with many native plugins (camera, notifications, purchases).
- **Env file required for dev** — `.env` loaded at runtime, not bundled as asset.

## External dependencies

| Service | Purpose |
|---------|---------|
| Firebase Auth | User authentication |
| Cloud Firestore | User data, community, nutrition |
| Firebase Storage | Remote audio (NSDR) |
| Firebase Messaging | Push notifications |
| RevenueCat | Subscription purchases |
| Superwall | Paywall presentation |
| Mixpanel | Product analytics |
| Groq API | Food scan vision, chatbot STT |
| OpenAI API | Chatbot TTS |
| Edamam / Spoonacular | Recipes and nutrition |
| AppsFlyer | Attribution and promos |

## Access control

- `SubscriptionService.isPaidSubscriber()` gates premium features.
- Debug, TestFlight, and internal builds bypass subscription checks.
- Firebase subscription fields are analytics-only, not used for gating.

## What this guide does not cover

- Firebase security rules deployment.
- App Store / Play Store release process.
- Superwall dashboard configuration.
