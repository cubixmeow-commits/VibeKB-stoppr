---
id: env-driven-configuration
type: constraint
title: Environment-driven API configuration
summary: API keys and Firebase config load from .env at runtime via EnvConfig; not bundled as assets.
status: active
verification: verified-from-source
functionality: [app-startup]
files: [lib/core/config/env_config.dart, lib/firebase_options.dart]
updated: 2026-07-21
---

## Source

`main.dart` loads `.env` with flutter_dotenv. Production builds expect build-time or secure config.

## Consequences

- Repository clones without `.env` will have missing integrations.
- VibeKB and docs must never expose actual key values.
- Placeholder fallbacks exist for some OAuth client IDs.
