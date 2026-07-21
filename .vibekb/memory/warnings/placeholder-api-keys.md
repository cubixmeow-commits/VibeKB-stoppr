---
id: placeholder-api-keys
type: warning
title: Placeholder API keys and OAuth client IDs
summary: Several integrations use placeholder fallback strings when env vars are missing.
severity: high
verification: verified-from-source
functionality: [app-startup, firebase-auth, sugar-food-scanning]
files: [lib/core/auth/auth_service.dart, lib/core/config/env_config.dart]
updated: 2026-07-21
---

## What can go wrong

Google OAuth, Superwall placements, and widget app group IDs may use placeholder values, causing auth failures or broken widgets.

## Do not

Copy placeholder values from source into documentation as working configuration.
