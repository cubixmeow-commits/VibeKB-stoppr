---
id: google-oauth-placeholders
type: warning
title: Google OAuth client ID fallbacks
summary: auth_service.dart embeds placeholder OAuth client ID strings when EnvConfig is null.
severity: medium
verification: verified-from-source
functionality: [firebase-auth]
files: [lib/core/auth/auth_service.dart]
updated: 2026-07-21
---

## What can go wrong

Google Sign-In fails on device without real client IDs.

## Safe next action

Supply `GOOGLE_OAUTH_CLIENT_ID_IOS` / `GOOGLE_OAUTH_SERVER_CLIENT_ID_ANDROID`
via env; remove need for fallbacks when ready.
