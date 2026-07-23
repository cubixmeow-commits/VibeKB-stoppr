---
id: app-startup
type: functionality
title: Application startup
area: app-core
summary: Initializes Flutter bindings, env, RevenueCat, Firebase, analytics, notifications, and platform SDKs before runApp.
status: implemented
verification: verified-from-source
user_facing: false
trigger: User launches the app.
files: [lib/main.dart, lib/firebase_options.dart, lib/core/config/env_config.dart]
reads: []
writes: []
config: []
depends_on: []
related_memory: [warning:placeholder-api-keys, warning:env-file-mismatch]
created: 2026-07-21
updated: 2026-07-23
tags: []
---

## In one sentence

Cold start wires SDKs, then shows a loading scaffold until routing resolves.

## Current behavior

`main()` in `lib/main.dart` calls `WidgetsFlutterBinding.ensureInitialized()`,
loads `.env` via flutter_dotenv (non-fatal on failure), configures RevenueCat
early from `EnvConfig`, sets OpenAI key if present, initializes Firebase with
`DefaultFirebaseOptions`, Crashlytics, NotificationService, Crisp, Quick
Actions, Mixpanel, AppsFlyer, FCM, and platform Superwall setup, then
`runApp(MyApp)`.

## Step-by-step flow

1. Ensure Flutter binding.
2. Load dotenv `.env`.
3. Early `Purchases.configure` using platform RevenueCat key names.
4. `Firebase.initializeApp` via `firebase_options.dart` (non-null env getters).
5. Initialize notifications, analytics, messaging, and related services.
6. Construct `MyApp` with `AuthCubit` / shared preferences and run.

## Failure cases

- Missing Firebase env values can throw due to `!` in `firebase_options.dart`.
- Missing optional keys log warnings; some services skip init.

## Current state

Implemented. Verification: verified-from-source (`main()` traced).

## Use caution

Do not reorder RevenueCat readiness flags without checking NotificationService
and Superwall purchase controller assumptions.
