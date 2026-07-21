---
id: mobile-only-platform
type: constraint
title: Mobile-only platform support
summary: firebase_options.dart throws for web, macOS, Windows, and Linux targets.
status: active
verification: verified-from-source
functionality: [app-startup]
files: [lib/firebase_options.dart]
updated: 2026-07-21
---

## Source

Platform switch in `DefaultFirebaseOptions.currentPlatform` only implements Android and iOS.

## Consequences

VibeKB guide targets mobile behavior only. Desktop/web builds are out of scope.
