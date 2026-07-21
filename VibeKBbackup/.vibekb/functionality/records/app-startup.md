---
area: app-core
summary: Initializes Flutter, Firebase, RevenueCat, analytics, notifications, and third-party SDKs before runApp.
status: implemented
verification: verified-from-source
user_facing: false
trigger: User launches the app.
files: [lib/main.dart, lib/firebase_options.dart, lib/core/config/env_config.dart]
reads: []
writes: [users]
config: [.env, MIXPANEL_API_KEY, REVENUECAT_IOS_API_KEY, REVENUECAT_ANDROID_API_KEY]
depends_on: []
related_memory: [warning:placeholder-api-keys]
id: app-startup
type: functionality
title: Application startup
updated: 2026-07-21
---

## In one sentence

Cold start wires every SDK before the first frame.

## Current behavior

`main()` loads `.env`, configures RevenueCat early, initializes Firebase, Crashlytics (prod), NotificationService, Crisp, QuickActions, Mixpanel, AppsFlyer, FCM, and Superwall (Android). Camera list is fetched globally.

## Step-by-step flow

1. `WidgetsFlutterBinding.ensureInitialized()`
2. Load `.env` via flutter_dotenv (non-fatal on failure)
3. Early `Purchases.configure()` from EnvConfig
4. `Firebase.initializeApp()` with DefaultFirebaseOptions
5. Initialize notifications, analytics, messaging
6. `runApp(MyApp)` with AuthCubit

## Failure cases

- Missing `.env` keys log warnings; some services skip init.
- Firebase init failure prevents normal operation.

## Safe to change

Debug flags, non-critical service init order.

## Use caution

RevenueCat early init order — NotificationService depends on readiness flag.
