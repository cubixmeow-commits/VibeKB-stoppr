---
id: no-automated-tests
type: discovery
title: No automated test suite in repository
summary: flutter_test is a dev dependency but no test/ directory or *_test.dart files exist.
changed_model: deployment
verification: verified-from-source
functionality: [app-startup]
files: [pubspec.yaml]
updated: 2026-07-21
---

## Evidence

Glob search finds zero `*_test.dart` files. README documents `flutter test` but nothing to run.

## Impact

All VibeKB verification states are source-based, not test-verified.
