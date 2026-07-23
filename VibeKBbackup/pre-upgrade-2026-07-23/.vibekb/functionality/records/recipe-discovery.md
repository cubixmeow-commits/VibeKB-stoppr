---
id: recipe-discovery
type: functionality
title: Recipe discovery
area: nutrition
summary: "Searches Edamam first with Spoonacular fallback; favorites stored under users/{uid}/favorite_recipes."
status: implemented
verification: verified-from-source
user_facing: true
trigger: User browses or favorites recipes.
files: [lib/features/recipes/data/repositories/recipe_repository.dart, lib/features/recipes/presentation/screens/recipes_list_screen.dart]
reads: []
writes: []
config: []
depends_on: []
related_memory: []
created: 2026-07-21
updated: 2026-07-21
tags: []
---

## In one sentence

External recipe APIs back the recipes feature with local favorite sync.

## Current behavior

Uses `EDAMAM_API_KEY` / `EDAMAM_APP_ID` and `SPOONACULAR_API_KEY` config names.
Favorites persist in Firestore.

## Current state

Implemented; verified-from-source.
