---
area: education
summary: Bundled markdown articles in assets/ with progress tracking; Firestore articles collection unused by app.
status: implemented
verification: verified-from-source
user_facing: true
trigger: User opens Articles from home.
files: [lib/features/learn/data/services/article_service.dart, lib/features/learn/presentation/screens/articles_list_screen.dart]
reads: [shared_preferences]
writes: [shared_preferences]
depends_on: [home-dashboard]
related_memory: []
id: articles-education
type: functionality
title: Articles and educational content
updated: 2026-07-21
---

## In one sentence

Bundled markdown articles in assets/ with progress tracking; Firestore articles collection unused by app.

## Current behavior

Implemented in source per files listed in front matter. Runtime behavior depends on Firebase and API configuration.

## Current state

**Status:** implemented. **Verification:** verified-from-source.

## Safe to change

Presentation and copy with localization.

## Use caution

Data writes and subscription checks.
