---
id: imperative-navigation
type: decision
title: Imperative Navigator routing
summary: Primary navigation uses Navigator push/replace rather than a single declarative router graph.
status: active
verification: verified-from-source
functionality: [startup-routing, main-navigation, deep-link-handling]
files: [lib/main.dart]
alternatives: [Full GoRouter migration]
updated: 2026-07-21
---

## Decision

Startup selects a root widget; subsequent flows push screens imperatively.
Deep links mutate navigation from `main.dart` handlers.
