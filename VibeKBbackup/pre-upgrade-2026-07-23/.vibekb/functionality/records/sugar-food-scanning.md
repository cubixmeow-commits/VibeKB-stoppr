---
id: sugar-food-scanning
type: functionality
title: Sugar food scanning
area: nutrition
summary: Vision analysis via Groq-compatible endpoint with local image cache and optional Firebase Storage fallback.
status: implemented
verification: verified-from-source
user_facing: true
trigger: User opens food scanner and captures or selects an image.
files: [lib/features/nutrition/presentation/screens/food_scanner_screen.dart, lib/core/services/local_food_image_service.dart]
reads: []
writes: []
config: []
depends_on: [calorie-nutrition-tracking]
related_memory: [warning:placeholder-api-keys]
created: 2026-07-21
updated: 2026-07-21
tags: []
---

## In one sentence

Camera/gallery images are analyzed for nutrition estimates then logged.

## Current behavior

Uses `GROQ_API_KEY`. Quota soft-paywall code present but disabled. Images cached
locally via `LocalFoodImageService`.

## Current state

Implemented; verified-from-source. Runtime accuracy of model output not verified.
