---
id: env-driven-configuration
type: constraint
title: Environment-driven secrets
summary: API keys and OAuth clients come from dotenv / platform templates; values must not be committed or documented.
status: active
functionality: [app-startup, firebase-auth]
files: [lib/core/config/env_config.dart, .gitignore]
updated: 2026-07-21
---

## Source

`.gitignore` excludes `.env` and live Firebase/Google plist/json files while
allowing `.local` templates.

## Consequences

Documentation may name variables (e.g. `SUPERWALL_IOS_API_KEY`) but never
values.
