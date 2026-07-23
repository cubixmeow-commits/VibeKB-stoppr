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

1. Start with `php tools/vibekb.php status`.
2. Read `work/handoff.md` and `work/current.md`.
3. Inspect source under `lib/` — not README alone.
4. Edit records in `.vibekb/`; keep statuses and verification honest.
5. Update Explainable Diagrams if the picture changed.
6. Validate and regenerate:

```bash
php tools/vibekb.php check
php tools/test-topology.php
php tools/vibekb.php generate
```

## Do not

- Copy SousMeow example content into Stoppr records
- Expose secrets from `.env` or config
- Claim manual testing without running the app
- Modify Stoppr application code as part of VibeKB maintenance
- Hand-edit generated HTML under `/docs`

## Schema reference

See [`SCHEMA.md`](SCHEMA.md), [`INSTALLER.md`](INSTALLER.md), and upstream
[VibeKB](https://github.com/cubixmeow-commits/VibeKB).
