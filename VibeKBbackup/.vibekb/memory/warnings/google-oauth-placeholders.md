---
id: google-oauth-placeholders
type: warning
title: Google OAuth client ID fallbacks
summary: auth_service.dart contains placeholder OAuth client ID strings when env config is missing.
severity: medium
verification: verified-from-source
functionality: [firebase-auth]
files: [lib/core/auth/auth_service.dart]
updated: 2026-07-21
---

## What can go wrong

Google Sign-In will fail on devices if real OAuth client IDs are not provided via environment configuration.
