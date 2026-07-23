---
id: change-vibekb-0-2-0-safe-integration
type: change
title: Migrate Stoppr to VibeKB 0.2.0 safe repository layout
date: 2026-07-23
status: completed
verification_state: verified-from-source
related_functionality: []
---

Migrated this repository from the pre-0.2.0 root-level VibeKB install (runtime
at `guide/` + `tools/`, reference docs at repo root) to the VibeKB **0.2.0**
consolidated layout via `vibekb migrate` from CLI tip `683df05`.

What changed:

- VibeKB-owned runtime, reference docs, and prompts live under `.vibekb/`.
- `AGENTS.md` / `CLAUDE.md` keep Stoppr-owned content; VibeKB only maintains a
  marked managed block.
- `.cursor/rules/vibekb.mdc` is the namespaced Cursor adapter.
- Unmodified root VibeKB files were removed; Stoppr pointers updated.
- Static snapshot remains at `/docs` for GitHub Pages Mode B.
- Topology test discovers local diagram fixtures (Stoppr has no
  `self-maintenance-loop` / `content-load-flow`).

Stoppr application code under `lib/` was not modified.
