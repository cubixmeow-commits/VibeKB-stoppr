---
id: chatbot-assistant
type: functionality
title: Melinda chatbot assistant
area: education
summary: CBT-oriented AI assistant using Groq chat/transcription and OpenAI speech, limited to 20 API interactions per day.
status: implemented
verification: verified-from-source
user_facing: true
trigger: User opens the Melinda chatbot screen.
files: [lib/features/app/presentation/screens/chatbot/chatbot_screen.dart, lib/core/api_rate_limit/api_rate_limit_service.dart]
reads: []
writes: []
config: []
depends_on: [soft-paywalls-quotas]
related_memory: []
created: 2026-07-21
updated: 2026-07-21
tags: []
---

## In one sentence

Melinda personalizes prompts from streak/onboarding data and calls Groq/OpenAI.

## Current behavior

Builds context from user state, enforces `ApiRateLimitService` (20/day, not
subscription-aware), optional FeatureQuota (disabled). Uses config names
`GROQ_API_KEY` and `OPENAI_API_KEY` only — values must not be documented.

## Current state

Implemented; verified-from-source.
