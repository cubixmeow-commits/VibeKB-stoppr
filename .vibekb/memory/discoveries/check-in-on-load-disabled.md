---
id: check-in-on-load-disabled
type: discovery
title: Daily check-in on home load is disabled
summary: MainScaffold passes showCheckInOnLoad false with debug comment.
changed_model: daily-check-in-pledge
verification: verified-from-source
functionality: [daily-check-in-pledge, home-dashboard]
files: [lib/features/app/presentation/screens/main_scaffold.dart]
updated: 2026-07-21
---

## Evidence

`main_scaffold.dart` sets `showCheckInOnLoad: false` with comment "Disabled check-in on load for debug testing".

## Impact

Users may not see automatic daily check-in overlay on app open despite widget existing.
