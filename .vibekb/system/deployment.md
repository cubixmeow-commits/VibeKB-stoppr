---
id: deployment
type: system
title: Deployment
summary: Stoppr ships as iOS and Android Flutter builds with env-configured API keys; Firebase rules and indexes live in repo root; VibeKB guide hosts statically from /docs.
verification: verified-from-source
updated: 2026-07-21
---

## Mobile app deployment

- **Build**: `flutter build apk` / `flutter build ios` (per `CLAUDE.md`).
- **Config**: Runtime `.env` for API keys; `lib/firebase_options.dart` reads env vars.
- **Firebase**: `firestore.rules`, `firestore.indexes.json`, `firebase.json.local` in repo.
- **Version**: `7.4.2+1` in `pubspec.yaml`.

## Environment requirements

Developers need configured keys for: Firebase, RevenueCat, Superwall, Mixpanel,
OpenAI, Groq, AppsFlyer, Edamam, Spoonacular, Google OAuth, Crisp.

Placeholder strings exist when env vars are missing — app may fail or degrade.

## VibeKB guide deployment

This repository includes a static guide in `/docs` generated from `.vibekb/`.
GitHub Pages can serve it from branch `main`, folder `/docs`.

Regenerate after content changes:

```bash
python3 .vibekb/tools/generate_docs.py
```

## CI / testing

No automated test suite present in repository. `flutter analyze` and manual QA
are the documented verification paths.
