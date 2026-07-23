---
id: calorie-nutrition-tracking
type: functionality
title: Calorie nutrition tracking
area: nutrition
summary: Firestore food_logs, daily_summaries, nutrition goals, body metrics, and workout logs under the user document.
status: implemented
verification: verified-from-source
user_facing: true
trigger: User views calorie dashboard or logs food/workouts.
files: [lib/features/nutrition/data/repositories/nutrition_repository.dart, lib/features/nutrition/presentation/screens/calorie_tracker_dashboard.dart]
reads: []
writes: []
config: []
depends_on: [firebase-auth]
related_memory: []
created: 2026-07-21
updated: 2026-07-23
tags: []
---

## In one sentence

NutritionRepository is the persistence hub for calorie tracking.

## Current behavior

CRUD for food logs and summaries; goals and body metrics documents; may ensure
anonymous auth. Manual entry screens can call OpenAI/Groq for text estimates.

## Current state

Implemented; verified-from-source.
