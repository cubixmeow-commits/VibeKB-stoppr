---
id: system-mental-model
type: system
title: Mental model
summary: A Flutter client where Firebase holds user data, RevenueCat/Superwall gate premium, and local preferences plus widgets keep daily habits fast.
updated: 2026-07-21
---

## Picture this

Stoppr is a phone app. On launch it wakes Firebase and purchase SDKs, figures
out whether you finished onboarding and whether you are paid, then either
continues onboarding, shows the paywall, or opens the home hub.

Daily life happens on Home: streak, pledge, panic. Deeper tools (community,
learn, nutrition, challenge) hang off navigation. Money questions always go
through Superwall UI and RevenueCat entitlements — Firestore remembers
subscription fields but does not decide access.
