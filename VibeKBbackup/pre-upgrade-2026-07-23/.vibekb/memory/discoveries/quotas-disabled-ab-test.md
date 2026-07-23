---
id: quotas-disabled-ab-test
type: discovery
title: Feature quotas disabled via local flags
summary: QUOTA_SYSTEM_ENABLED is hard-coded false on major premium feature screens.
verification: verified-from-source
functionality: [soft-paywalls-quotas, learn-video-lessons, chatbot-assistant]
files: [lib/features/learn/presentation/screens/learn_video_list_screen.dart]
changed_model: true
updated: 2026-07-21
---

## Evidence

Local `const bool QUOTA_SYSTEM_ENABLED = false` in learn, food scan, Rate My
Plate, and chatbot screens. FeatureQuotaService still exists.

## Impact on model

Soft quota paywalls are `partial`, not fully active behavior.
