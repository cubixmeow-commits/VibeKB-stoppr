# VibeKB maintenance for Stoppr

Prefer the canonical workflow in [`MAINTENANCE.md`](MAINTENANCE.md). This file
adds Stoppr-specific notes.

## When to update

Update `.vibekb/` when behavior users or developers would notice changes:

- New screens or flows
- Subscription or auth changes
- Firestore schema changes
- Disabled or experimental features
- New external integrations
- Diagram topology / SVG changes

## Workflow

1. Read `work/handoff.md` and `work/current.md`.
2. Inspect source under `lib/` — not README alone.
3. Edit records in `.vibekb/`; keep statuses and verification honest.
4. Update Explainable Diagrams if the picture changed.
5. Validate and regenerate:

```bash
php tools/validate.php
php tools/test-topology.php
php tools/generate-static.php
```

## Do not

- Copy SousMeow example content into Stoppr records
- Expose secrets from `.env` or config
- Claim manual testing without running the app
- Modify Stoppr application code as part of VibeKB maintenance
- Hand-edit generated HTML under `/docs`

## Schema reference

See [`SCHEMA.md`](SCHEMA.md) and upstream
[VibeKB](https://github.com/cubixmeow-commits/VibeKB).
