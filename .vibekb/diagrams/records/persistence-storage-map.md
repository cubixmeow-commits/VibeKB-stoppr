---
id: persistence-storage-map
type: diagram
title: Persistence and storage map
summary: Feature writers to Firestore, SharedPreferences, HomeWidget, and Firebase Storage.
diagram_type: storage-map
group: technical-system
svg: persistence-storage-map.svg
topology: persistence-storage-map.json
functionality: [calorie-nutrition-tracking, sugar-streak-tracking, home-widgets, community-forum]
files: [lib/core/streak/streak_service.dart]
data: [Firestore, SharedPreferences, Firebase Storage, HomeWidget]
warnings: [widget-app-group-placeholder]
diagrams: [app-overview]
status: implemented
verification: inferred-from-source
provenance: "Repository write paths verified; widget native delivery inferred."
last_verified: 2026-07-21
uncertainty: "App group placeholder may prevent iOS widget sync."
created: 2026-07-21
updated: 2026-07-23
---

## What am I looking at?

Where Stoppr persists state across cloud, device, widgets, and image storage.

## Why it matters

Prevents confusing Firestore entitlement mirrors with local habit state.

## What is uncertain

Dashed widget/storage edges and the placeholder app group warning.
