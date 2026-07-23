---
id: sugar-food-scanning
type: functionality
title: Sugar food scanning
area: nutrition
summary: Vision analysis via Groq-compatible endpoint with local image cache and optional Firebase Storage fallback; nutrition path uses ApiRateLimitService (not FeatureQuota soft-paywall).
status: implemented
verification: verified-from-source
user_facing: true
trigger: User opens food scanner and captures or selects an image.
files: [lib/features/nutrition/presentation/screens/food_scanner_screen.dart, lib/features/app/presentation/screens/food_scan/food_scan_screen.dart, lib/core/services/local_food_image_service.dart, lib/core/api_rate_limit/api_rate_limit_service.dart]
reads: []
writes: []
config: []
depends_on: [calorie-nutrition-tracking]
related_memory: [warning:placeholder-api-keys, discovery:quotas-disabled-ab-test]
created: 2026-07-21
updated: 2026-07-23
tags: []
---

## In one sentence

Camera/gallery images are analyzed for nutrition estimates then logged.

## Current behavior

Uses `GROQ_API_KEY`. The nutrition `food_scanner_screen` path relies on
`ApiRateLimitService` rather than the disabled `FeatureQuotaService` soft
paywall. A separate sugar-scan UI also exists under
`lib/features/app/presentation/screens/food_scan/` (with Superwall
placeholder placements when quotas/offers fire). Images are cached locally via
`LocalFoodImageService`.

## Current state

Implemented; verified-from-source (re-checked 2026-07-23). Runtime accuracy of
model output not verified.
