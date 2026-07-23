---
id: deep-link-handling
type: functionality
title: Deep link handling
area: app-core
summary: Processes payment success, home/pledge/panic/meditation schemes, share invites, and promo deep links; some declared routes lack handlers.
status: partial
verification: verified-from-source
user_facing: true
trigger: Incoming App Link / custom scheme / AppsFlyer OneLink / widget URI.
files: [lib/main.dart, lib/core/streak/sharing_service.dart, android/app/src/main/AndroidManifest.xml]
reads: []
writes: []
config: []
depends_on: [startup-routing]
related_memory: [discovery:deep-link-handler-gaps]
created: 2026-07-21
updated: 2026-07-23
tags: []
---

## In one sentence

`_processDeepLink` routes known URIs; platform manifests declare additional
paths that the Dart handler does not fully cover.

## Current behavior

Handled in source: `https://stoppr.app/payment/success`, `stoppr://home`,
`stoppr://pledge`, `stoppr://panic`, `stoppr://meditation`, share tokens via
`SharingService`, and AppsFlyer promo application.

## Gaps

- Android declares `stoppr://payment/success` and `/winback` without matching
  Dart handlers (winback noted removed).
- Widget URI `stoppr://accountability` is emitted by iOS widgets but not
  handled in `_processDeepLink`.

## Current state

Partial. Verification: verified-from-source.
