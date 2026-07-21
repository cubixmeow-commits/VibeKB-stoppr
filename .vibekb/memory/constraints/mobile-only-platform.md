---
id: mobile-only-platform
type: constraint
title: Mobile Flutter client
summary: Stoppr ships as iOS/Android Flutter; VibeKB docs are static HTML, not part of the app binary.
status: active
functionality: [app-startup]
files: [pubspec.yaml]
updated: 2026-07-21
---

## Consequences

Do not assume browser APIs. Widget and purchase behavior are platform-specific.
