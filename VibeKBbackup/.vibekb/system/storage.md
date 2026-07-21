---
id: storage
type: system
title: Storage
summary: Stoppr uses Firestore for cloud user/community/nutrition data, SharedPreferences for local state, and FlutterSecureStorage for install tracking.
verification: verified-from-source
updated: 2026-07-21
---

## Cloud Firestore

### Top-level collections

| Collection | Purpose |
|------------|---------|
| `users` | User profiles and metadata |
| `community_posts` | Forum posts |
| `official_chat` / `official_chat_{lang}` | Language chat rooms |
| `accountability_partnerships` | Partner pairs |
| `accountability_pool` | Matchmaking queue |
| `sharing_tokens` | Streak share invites |
| `user_feature_quotas` | Free-tier usage limits |

### `users/{uid}` subcollections

`onboarding`, `pledges`, `challenges/tasks`, `food_logs`, `daily_summaries`,
`nutrition_profile`, `body_metrics/weight_entries`, `workout_logs`,
`favorite_recipes`, `blocked_users`.

## Local storage (SharedPreferences)

Key examples: `streak_start_timestamp`, `onboarding_current_screen`,
`onboarding_completed`, `completed_learn_video_lessons`, `fasting_logs_v1`,
per-notification-type toggles, feature quota counters.

## Secure storage

`InstallationTrackerService` uses iOS Keychain (`permanent_install_uuid`) for
redownload detection.

## Firebase Storage

Remote audio files (e.g. NSDR track) via `RemoteAudioService`.

## What is NOT stored in Firestore

- Fasting logs (local only despite rules existing).
- Article content (bundled in `assets/articles/`).
- Video lesson metadata (hardcoded in `LearnVideoCubit`).
