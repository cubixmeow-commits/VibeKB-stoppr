---
area: nutrition
summary: Camera capture analyzed via Groq vision API with multi-slide alternatives presentation.
status: implemented
verification: verified-from-source
user_facing: true
trigger: User opens Food Scan from home.
files: [lib/features/app/presentation/screens/food_scan/food_scan_screen.dart, lib/features/app/presentation/screens/food_scan/food_alternatives_screen.dart]
reads: []
writes: [user_feature_quotas]
config: [GROQ_API_KEY]
depends_on: [home-dashboard, soft-paywalls-quotas]
related_memory: [discovery:quotas-disabled-ab-test]
id: sugar-food-scanning
type: functionality
title: Sugar food scanning
updated: 2026-07-21
---

## In one sentence

Camera capture analyzed via Groq vision API with multi-slide alternatives presentation.

## Current behavior

Implemented in source per files listed in front matter. Runtime behavior depends on Firebase and API configuration.

## Current state

**Status:** implemented. **Verification:** verified-from-source.

## Safe to change

Presentation and copy with localization.

## Use caution

Data writes and subscription checks.
