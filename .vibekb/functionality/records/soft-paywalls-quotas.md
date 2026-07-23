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
files: [lib/core/usage/feature_quota_service.dart, lib/core/api_rate_limit/api_rate_limit_service.dart, lib/features/learn/presentation/screens/learn_video_list_screen.dart, lib/features/app/presentation/screens/food_scan/food_scan_screen.dart, lib/features/app/presentation/screens/food_scan/food_alternatives_screen.dart, lib/features/app/presentation/screens/rate_my_plate/rate_my_plate_scan_screen.dart, lib/features/app/presentation/screens/chatbot/chatbot_screen.dart, lib/features/onboarding/presentation/screens/give_us_ratings_screen.dart, lib/core/quick_actions/quick_actions_service.dart, lib/features/app/presentation/screens/home_screen.dart]
reads: []
writes: []
config: []
depends_on: [main-paywall]
related_memory: [discovery:quotas-disabled-ab-test, warning:superwall-placement-placeholders]
created: 2026-07-21
updated: 2026-07-23
tags: []
---

## In one sentence

Quota soft-paywalls are coded but currently disabled by local feature flags.

## Current behavior

`FeatureQuotaService` tracks limits in `user_feature_quotas` with
SharedPreferences fallback. Learn, food scan, Rate My Plate, and chatbot set
`QUOTA_SYSTEM_ENABLED = false`. Contextual placements (ratings, redownload,
quick actions, home banners) still call Superwall with placeholder or concrete
IDs. Chatbot (and other AI surfaces) separately enforce
`ApiRateLimitService` (20/day) even while feature quotas are off.

## Current state

Partial; verified-from-source (re-checked 2026-07-23).
