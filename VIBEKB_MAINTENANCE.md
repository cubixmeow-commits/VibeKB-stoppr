# VibeKB maintenance for Stoppr

## When to update

Update `.vibekb/` when you change behavior that users or developers would notice:

- New screens or flows
- Subscription or auth changes
- Firestore schema changes
- Disabled or experimental features
- New external integrations

## Workflow

1. **Read** `work/handoff.md` and `work/current.md` before starting.
2. **Inspect source** — verify claims against `lib/`, not README alone.
3. **Edit records** in `.vibekb/functionality/records/` or add memory in `.vibekb/memory/`.
4. **Set honest status and verification:**
   - `verified-from-source` — traced in code
   - `inferred-from-source` — strong code evidence, not executed
   - `not-verified` — unknown runtime behavior
5. **Update** `work/current.md` during active AI work.
6. **Validate** relationships, statuses, and file path references in `.vibekb/`.
7. **Update `/docs`** static HTML when the published guide must reflect model changes.

## Record types

| Directory | Purpose |
|-----------|---------|
| `project/` | Identity, intent, current state, constraints |
| `functionality/records/` | Primary unit — what the software does |
| `system/` | Architecture, flows, storage |
| `files/important-files.json` | Curated high-impact files |
| `memory/` | Decisions, warnings, assumptions, discoveries, changes |
| `work/` | Current AI work, handoff, sessions |

## Functionality record checklist

Each record should cover: trigger, flow, screens, state, files, data read/written, dependencies, failure cases, subscription restrictions, status, verification, and evidence.

## Do not

- Copy SousMeow or other example content
- Expose secrets from `.env` or config
- Claim manual testing without running the app
- Modify Stoppr application code as part of VibeKB maintenance
- Create `/guide` — public output is `/docs` only

## Schema reference

See the upstream [VibeKB SCHEMA.md](https://github.com/cubixmeow-commits/VibeKB/blob/main/SCHEMA.md) for field definitions and controlled vocabularies.
