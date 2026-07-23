---
id: rate-my-plate
type: functionality
title: Rate My Plate
area: nutrition
summary: Plate photo scoring via Groq vision and OpenAI results; quota gate present but disabled.
status: implemented
verification: verified-from-source
user_facing: true
trigger: User opens Rate My Plate scan/results.
files: [lib/features/app/presentation/screens/rate_my_plate/rate_my_plate_scan_screen.dart, lib/features/app/presentation/screens/rate_my_plate/rate_my_plate_results_screen.dart]
reads: []
writes: []
config: []
depends_on: [soft-paywalls-quotas]
related_memory: [discovery:quotas-disabled-ab-test]
created: 2026-07-21
updated: 2026-07-21
tags: []
---

## In one sentence

Scan screen analyzes a plate image; results screen elaborates scoring.

## Current state

Implemented; verified-from-source.
