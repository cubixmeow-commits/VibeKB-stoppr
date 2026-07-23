---
id: articles-education
type: functionality
title: Articles education
area: education
summary: Loads article content from assets and syncs reading progress to SharedPreferences and Firestore.
status: implemented
verification: verified-from-source
user_facing: true
trigger: User opens Articles list/detail.
files: [lib/features/learn/data/services/article_service.dart, lib/features/learn/presentation/screens/articles_list_screen.dart]
reads: []
writes: []
config: []
depends_on: []
related_memory: []
created: 2026-07-21
updated: 2026-07-23
tags: []
---

## In one sentence

Article progress syncs to `users/{uid}/progress/articles` when authenticated.

## Current state

Implemented; verified-from-source.
