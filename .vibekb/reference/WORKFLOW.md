# WORKFLOW.md — VibeKB operating rules for coding agents (installed copy)

This is the canonical operating document for every coding agent working on this
repository through VibeKB. It is installed by VibeKB under `.vibekb/reference/`
and is safe to read at the start of any session. Adapter files elsewhere in the
repo (an `AGENTS.md`/`CLAUDE.md` managed block, `.cursor/rules/vibekb.mdc`,
`.github/instructions/vibekb.instructions.md`) are thin pointers to this file.

## Start every session with one command

```bash
vibekb status
# or, without the CLI on PATH:
php .vibekb/runtime/tools/vibekb.php status
```

It prints the active model's provenance, the current work record, the handoff's
next recommended action, and a one-line validation + drift summary.

## What VibeKB is (and is not)

VibeKB exists so anyone can open this project at any point in its life and
understand **what the software is currently doing**, organised around
**functionality**. It is not a docs generator, a repository-memory archive, a
code browser, or an AI activity log. See `.vibekb/reference/PRODUCT.md`.

VibeKB is **agent-maintained**: it detects that code changed but never claims to
interpret a change on its own, and never implies it auto-updates.

## The maintenance lifecycle (follow it for every behaviour change)

1. **Orient.** `vibekb status`. Read the current functionality, affected records,
   active warnings, and the handoff's next action.
2. **Record the work.** Before implementing, update `.vibekb/work/current.md`.
3. **Implement** the code change.
4. **Find what it affects.** `vibekb affected --since <base>` (or pass files).
5. **Verify.** Trace or exercise the real behaviour; set an honest verification
   state — never claim `verified-*` for something only inferred.
6. **Update the living model.** Bring the affected functionality records, system
   docs, files, diagrams, memory, and provenance into line. Writing code is not
   "done."
7. **Update the handoff.** `.vibekb/work/handoff.md`: state, completed work,
   verification, unresolved work, warnings, and the exact next action.
8. **Check.** `vibekb check` must be clean before you commit.

## Truth and provenance rules

- Distinguish intended, implemented, and verified behaviour everywhere.
- Do not describe planned functionality as implemented, or claim it works because
  a file exists or an AI said so.
- Mark uncertainty (`unknown`, `needs-verification`, `not-verified`, `broken`,
  `inferred-from-source`). Never fabricate source line numbers.

## Where things live (this repository)

- `.vibekb/` — the VibeKB-owned knowledge base and its tooling. Everything VibeKB
  manages is inside this directory (plus the marked adapter blocks noted above).
- `.vibekb/runtime/` — the PHP guide (Mode A) and the self-maintenance CLI.
- `.vibekb/reference/` — this file and the VibeKB reference docs (PRODUCT, SCHEMA,
  MAINTENANCE, INSTALLER, INITIALIZE).
- `.vibekb/prompts/INTEGRATE_VIBEKB.md` — the prompt that drives building/updating
  the model.
- `.vibekb/generated/` — the optional static snapshot (`vibekb generate`).

VibeKB never owns generic repository files. Your `README`, `AGENTS.md`,
`CLAUDE.md`, `.gitignore`, and application code remain yours; VibeKB only touches
clearly marked managed blocks, and only with your consent.
