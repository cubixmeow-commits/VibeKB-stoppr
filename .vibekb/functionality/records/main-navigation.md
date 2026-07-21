---
area: app-core
summary: MainScaffold hosts five tabs: Home, Learn, Rewire Brain, Community, Profile.
status: implemented
verification: verified-from-source
user_facing: true
trigger: User reaches MainScaffold after onboarding or as returning user.
files: [lib/features/app/presentation/screens/main_scaffold.dart, lib/core/navigation/app_router.dart]
reads: []
writes: []
depends_on: [startup-routing]
related_memory: [decision:imperative-navigation]
id: main-navigation
type: functionality
title: Main tab navigation
updated: 2026-07-21
---

## In one sentence

MainScaffold hosts five tabs: Home, Learn, Rewire Brain, Community, Profile.

## Current behavior

Implemented in source per files listed in front matter. Runtime behavior depends on Firebase and API configuration.

## Current state

**Status:** implemented. **Verification:** verified-from-source.

## Safe to change

Presentation and copy with localization.

## Use caution

Data writes and subscription checks.
