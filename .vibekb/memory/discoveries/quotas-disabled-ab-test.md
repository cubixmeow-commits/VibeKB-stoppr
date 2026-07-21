---
id: quotas-disabled-ab-test
type: discovery
title: Feature quotas disabled for A/B testing
summary: Food scan 50% gate, chatbot quota, and related checks are commented out or bypassed in source.
changed_model: soft-paywalls-quotas
verification: verified-from-source
functionality: [soft-paywalls-quotas, sugar-food-scanning]
files: [lib/features/app/presentation/screens/food_scan/food_scan_screen.dart, "lib/features/app/presentation/screens/chatbot/chatbot_screen.dart"]
updated: 2026-07-21
---

## Evidence

Comments in `food_scan_screen.dart` and `chatbot_screen.dart` indicate quota enforcement disabled for A/B test. FeatureQuotaService logic still exists but may not be invoked.

## Impact

Free users may have unlimited access to gated features until A/B concludes and code is re-enabled.
