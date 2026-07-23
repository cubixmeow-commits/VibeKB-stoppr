---
id: env-file-mismatch
type: warning
title: Repository has .env.local but main loads .env
summary: Only .env.local is present in the tree while main.dart loads dotenv .env.
severity: medium
verification: verified-from-source
functionality: [app-startup]
files: [lib/main.dart]
updated: 2026-07-21
---

## What can go wrong

Local/CI checkouts may boot without keys unless `.env` is created privately.

## Safe next action

Document the expected local file name for developers without committing secrets.
