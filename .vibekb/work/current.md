---
id: work-current
type: work
title: Current AI work
objective: Migrate Stoppr to VibeKB 0.2.0 safe/consolidated repository layout while preserving the living model and Mode B /docs.
status: completed
verification_state: verified-from-source
affected_functionality: []
expected_files: [.vibekb/, docs/, AGENTS.md, CLAUDE.md, VIBEKB.md, VIBEKB_MAINTENANCE.md, .cursor/rules/vibekb.mdc]
data_impact: none on app runtime data
risks: Upstream topology test is self-hosted-coupled; Mode B /docs preference is a Stoppr local adaptation of the runtime default.
updated: 2026-07-23
---

## Requested outcome

1. Push VibeKB 0.2.0 safe integration into `cubixmeow-commits/VibeKB-stoppr`.
2. Open a PR into `main`.
3. Do not modify Stoppr application code.

## Verification plan

- `vibekb doctor`
- `vibekb check`
- `php .vibekb/runtime/tools/test-topology.php`
- `vibekb generate`
- Confirm no `lib/` / app source diffs
