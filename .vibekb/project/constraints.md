---
id: project-constraints
type: project
title: Constraints
summary: Mobile Flutter client, env-driven secrets, GitHub Pages docs deployment for VibeKB, and no application-code changes during documentation work.
verification: verified-from-source
updated: 2026-07-23
---

## Platform

- Flutter mobile (iOS + Android). Not a web app.
- Secrets and Firebase/Google configs are env-driven or `.local` templates;
  real plist/json secrets are gitignored.

## Monetization source of truth

- `SubscriptionService.isPaidSubscriber` uses Superwall status and RevenueCat
  `CustomerInfo`. Firestore subscription fields are written for analytics /
  display but are not used to grant access.

## Documentation output

- Public VibeKB guide is the static `/docs` snapshot (GitHub Pages folder
  `/docs`). `.vibekb/` is the source of truth; regenerate with
  `php .vibekb/runtime/tools/vibekb.php generate`
  (or `php .vibekb/runtime/tools/generate-static.php`).

## Safety

- Never document secret values.
- Do not modify application source as part of VibeKB maintenance.
