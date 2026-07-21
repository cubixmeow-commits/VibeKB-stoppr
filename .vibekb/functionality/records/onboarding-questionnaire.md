---
area: onboarding
summary: 13-question survey collecting consumption habits; answers saved locally and to Firestore.
status: implemented
verification: verified-from-source
user_facing: true
trigger: User enters QuestionnaireScreen during onboarding.
files: [lib/features/onboarding/presentation/screens/questionnaire_screen.dart, lib/features/onboarding/domain/models/question_model.dart]
reads: [shared_preferences]
writes: [shared_preferences, users]
depends_on: [onboarding-intro]
related_memory: []
id: onboarding-questionnaire
type: functionality
title: Onboarding questionnaire
updated: 2026-07-21
---

## In one sentence

13-question survey collecting consumption habits; answers saved locally and to Firestore.

## Current behavior

Implemented in source per files listed in front matter. Runtime behavior depends on Firebase and API configuration.

## Current state

**Status:** implemented. **Verification:** verified-from-source.

## Safe to change

Presentation and copy with localization.

## Use caution

Data writes and subscription checks.
