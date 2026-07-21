---
id: onboarding-health-claims
type: assumption
title: Onboarding copy contains product health claims
summary: Analysis and benefit screens present health/behavioral claims as product messaging, not verified medical facts.
confidence: high
verification: verified-from-source
functionality: [onboarding-personalized-analysis]
invalidated_by: Clinical validation or regulatory review
next_check: When onboarding copy changes
updated: 2026-07-21
---

## Assumption

Screens like `analysis_result_screen.dart` and `stoppr_science_backed_plan.dart` display persuasive health framing. The code does not validate these claims against external medical evidence.

## For guide readers

Document what the app **shows**, not whether claims are scientifically established.
