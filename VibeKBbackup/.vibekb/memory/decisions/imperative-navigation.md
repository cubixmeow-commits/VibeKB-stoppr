---
id: imperative-navigation
type: decision
title: Imperative navigation instead of GoRouter
summary: Stoppr uses Navigator.push/pushReplacement throughout rather than a declarative route table.
status: active
verification: verified-from-source
functionality: [main-navigation, startup-routing]
files: [lib/main.dart, lib/core/navigation/app_router.dart]
alternatives: [GoRouter, auto_route]
updated: 2026-07-21
---

## Context

Flutter apps commonly use GoRouter or auto_route for deep linking and route tables.

## Decision

Stoppr uses imperative navigation. `AppRouter` only provides `createFadeRoute()`.
Hundreds of screens use `Navigator.push` with `RouteSettings` names for analytics.

## Reason

Historical codebase choice; deep links handled manually in `main.dart`.

## Consequences

- No single route map to consult — must trace Navigator calls.
- Deep link coverage is manual per URI pattern.
- Safe refactors require grep for screen class names.
