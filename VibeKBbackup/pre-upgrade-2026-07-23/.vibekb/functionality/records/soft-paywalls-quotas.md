---
id: soft-paywalls-quotas
type: functionality
title: Soft paywalls and quotas
area: subscription
summary: FeatureQuotaService and contextual Superwall placements exist, but QUOTA_SYSTEM_ENABLED is false on major feature screens.
status: partial
verification: verified-from-source
user_facing: true
trigger: User opens gated features or ratings/redownload/quick-action offers.
files: [lib/core/usage/feature_quota_service.dart, lib/features/learn/presentation/screens/learn_video_list_screen.dart, lib/features/app/presentation/screens/chatbot/chatbot_screen.dart, lib/features/onboarding/presentation/screens/give_us_ratings_screen.dart]
reads: []
writes: []
config: []
depends_on: [main-paywall]
related_memory: [discovery:quotas-disabled-ab-test, warning:superwall-placement-placeholders]
created: 2026-07-21
updated: 2026-07-21
tags: []
---

## In one sentence

Quota soft-paywalls are coded but currently disabled by local feature flags.

## Current behavior

`FeatureQuotaService` tracks limits in `user_feature_quotas` with
SharedPreferences fallback. Learn, food scan, Rate My Plate, and chatbot set
`QUOTA_SYSTEM_ENABLED = false`. Contextual placements (ratings, redownload,
quick actions, home banners) still call Superwall with placeholder or concrete
IDs. Chatbot separately enforces `ApiRateLimitService` (20/day).

## Current state

Partial; verified-from-source.
