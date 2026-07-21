---
id: system-data-flow
type: system
title: Data flow
summary: User actions update SharedPreferences and Firestore; purchases update RevenueCat then mirror fields to Firestore.
updated: 2026-07-21
---

## Paths

1. **Auth** → Firebase Auth → profile doc `users/{uid}` → RevenueCat logIn.
2. **Onboarding** → local progress keys ↔ Firestore onboarding subdocs.
3. **Purchase** → Superwall UI → RevenueCat purchase → Superwall status sync →
   Firestore subscription mirror fields → congratulations / MainScaffold.
4. **Daily habits** → local prefs + Firestore user/pledge/challenge docs →
   HomeWidget bridge.
5. **AI features** → device → Groq/OpenAI/Edamam/Spoonacular (API key names
   only) → results stored in nutrition/chat UIs.
