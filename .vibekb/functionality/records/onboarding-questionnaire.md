---
id: onboarding-questionnaire
type: functionality
title: Onboarding questionnaire
area: onboarding
summary: "Multi-page questionnaire saves answers locally and to users/{uid}/onboarding/questionnaire with resume support."
status: implemented
verification: verified-from-source
user_facing: true
trigger: User reaches QuestionnaireScreen in the funnel.
files: [lib/features/onboarding/presentation/screens/questionnaire_screen.dart, lib/features/onboarding/data/repositories/questionnaire_repository.dart, lib/features/onboarding/data/services/onboarding_progress_service.dart]
reads: []
writes: []
config: []
depends_on: [onboarding-intro]
related_memory: []
created: 2026-07-21
updated: 2026-07-21
tags: []
---

## In one sentence

Questionnaire answers drive later analysis and are persisted for resume.

## Current behavior

PageController-driven questions write SharedPreferences progress keys and
Firestore onboarding docs (questionnaire, consumption, acquisition). iOS may
request ATT during this flow.

## Current state

Implemented; verified-from-source.
