---
area: nutrition
summary: Edamam-primary recipe search with Spoonacular fallback and Firestore favorites.
status: implemented
verification: verified-from-source
user_facing: true
trigger: User opens Recipes from home.
files: [lib/features/recipes/data/repositories/recipe_repository.dart, lib/features/recipes/presentation/screens/recipes_list_screen.dart]
reads: [favorite_recipes]
writes: [favorite_recipes]
config: [EDAMAM_APP_ID, SPOONACULAR_API_KEY]
depends_on: [home-dashboard]
related_memory: []
id: recipe-discovery
type: functionality
title: Recipe discovery
updated: 2026-07-21
---

## In one sentence

Edamam-primary recipe search with Spoonacular fallback and Firestore favorites.

## Current behavior

Implemented in source per files listed in front matter. Runtime behavior depends on Firebase and API configuration.

## Current state

**Status:** implemented. **Verification:** verified-from-source.

## Safe to change

Presentation and copy with localization.

## Use caution

Data writes and subscription checks.
