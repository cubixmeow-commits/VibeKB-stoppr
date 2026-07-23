---
id: placeholder-api-keys
type: warning
title: Placeholder API keys and OAuth client IDs
summary: Integrations fall back to INSERT_YOUR_* strings when env vars are missing.
severity: high
verification: verified-from-source
functionality: [app-startup, firebase-auth, sugar-food-scanning]
files: [lib/core/auth/auth_service.dart, lib/core/config/env_config.dart]
updated: 2026-07-21
---

## What can go wrong

Auth, AI, or purchase SDKs fail or mis-configure if `.env` is absent. Do not
copy placeholder strings into docs as working values.

## Safe next action

Ensure CI/local builds provide real env files privately; keep placeholders out
of generated docs.
