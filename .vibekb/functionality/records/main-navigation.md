---
id: main-navigation
type: functionality
title: Main navigation
area: app-core
summary: Bottom-tab MainScaffold hosts home and primary feature surfaces with an initialIndex deep-link entry.
status: implemented
verification: verified-from-source
user_facing: true
trigger: User reaches MainScaffold after onboarding/paywall.
files: [lib/features/app/presentation/screens/main_scaffold.dart]
reads: []
writes: []
config: []
depends_on: [startup-routing]
related_memory: []
created: 2026-07-21
updated: 2026-07-23
tags: []
---

## In one sentence

`MainScaffold` is the post-paywall shell with indexed tabs and optional bar
hiding.

## Current behavior

Stateful scaffold with `initialIndex` and an **inline** bottom navigation
(when enabled). Active tabs: Home, Learn Videos, Rewire Brain, Community,
Profile. Lifecycle observers mark onboarding complete for authenticated users
when appropriate. The older
`lib/features/app/presentation/widgets/bottom_navigation.dart` file is not the
active tab builder.

## Current state

Implemented; verified-from-source (re-checked 2026-07-23).
