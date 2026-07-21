---
area: subscription
summary: FeatureQuotaService limits free-tier usage; soft paywalls on learn, food scan, chatbot, panic — partially disabled for A/B.
status: partial
verification: verified-from-source
user_facing: true
trigger: Free user hits quota threshold on a gated feature.
files: [lib/core/subscription/feature_quota_service.dart, lib/features/learn/presentation/screens/learn_video_list_screen.dart]
reads: [user_feature_quotas, shared_preferences]
writes: [user_feature_quotas, shared_preferences]
depends_on: [subscription-access-gating]
related_memory: [discovery:quotas-disabled-ab-test]
id: soft-paywalls-quotas
type: functionality
title: Soft paywalls and feature quotas
updated: 2026-07-21
---

## In one sentence

FeatureQuotaService limits free-tier usage; soft paywalls on learn, food scan, chatbot, panic — partially disabled for A/B.

## Current behavior

Implemented in source per files listed in front matter. Runtime behavior depends on Firebase and API configuration.

## Current state

**Status:** partial. **Verification:** verified-from-source.

## Safe to change

Presentation and copy with localization.

## Use caution

Data writes and subscription checks.
