---
id: anonymous-auth-spread
type: discovery
title: Anonymous auth created from multiple call sites
summary: signInAnonymously appears in onboarding and several repositories, not one gateway.
verification: verified-from-source
functionality: [guest-anonymous-access]
files: [lib/features/onboarding/presentation/screens/profile_info_screen.dart]
changed_model: true
updated: 2026-07-21
---

## Impact

Guest identity behavior can differ by entry feature; nutrition may write
`hasCompletedOnboarding` while onboarding uses `onboardingCompleted`.
