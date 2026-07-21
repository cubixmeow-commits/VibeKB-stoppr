---
id: data-flow
type: system
title: Data flow
summary: User actions flow from Flutter UI through services to Firestore or SharedPreferences, with subscription checks before premium writes.
verification: verified-from-source
updated: 2026-07-21
---

## Read path (typical)

1. Screen widget builds → reads Cubit state or calls service singleton.
2. Service checks SharedPreferences cache first (streak, onboarding, quotas).
3. If subscribed/authenticated, service may sync from Firestore `users/{uid}`.
4. Community/nutrition read directly from Firestore collections.

## Write path (typical)

1. User action triggers service method.
2. Service validates auth (may create anonymous user).
3. Subscription check for premium features (`SubscriptionService`).
4. Write to Firestore and/or SharedPreferences.
5. Mixpanel event fired (English event names).

## Onboarding data flow

```
QuestionnaireScreen → QuestionnaireRepository → SharedPreferences + Firestore onboarding subcollection
ProfileInfoScreen → UserRepository → users/{uid}
OnboardingProgressService → SharedPreferences + users/{uid}.lastOnboardingScreen
```

## Purchase data flow

```
Superwall placement → SuperwallPurchaseController → RevenueCat purchase
→ Firebase users/{uid} subscription fields (analytics)
→ PostPurchaseHandler → CongratulationsScreen → MainScaffold
```

## Offline behavior

- Streak timer runs locally from SharedPreferences timestamp.
- Many features show errors or empty states without network.
- No explicit offline queue for Firestore writes observed in source.
