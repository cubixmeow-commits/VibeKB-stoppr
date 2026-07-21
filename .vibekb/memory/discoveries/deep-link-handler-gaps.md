---
id: deep-link-handler-gaps
type: discovery
title: Manifest deep links without Dart handlers
summary: Some Android/iOS declared URIs are not handled in _processDeepLink.
verification: verified-from-source
functionality: [deep-link-handling]
files: [lib/main.dart, android/app/src/main/AndroidManifest.xml]
changed_model: true
updated: 2026-07-21
---

## Evidence

`/winback` declared but handler removed; `stoppr://accountability` used by
widgets without Dart case; scheme mismatch for payment success variants.
