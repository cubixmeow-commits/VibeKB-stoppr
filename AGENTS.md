# AGENTS.md — Guidelines for AI agents (Stoppr + VibeKB)

This file applies to every coding agent working in this repository.

## Two rulebooks

1. **Stoppr application code** — follow [`CLAUDE.md`](CLAUDE.md) (Flutter/Dart)
   and [`.cursor/rules/flutter.mdc`](.cursor/rules/flutter.mdc).
   Do not modify application behavior unless the owner explicitly asks.
2. **VibeKB living software model** — follow
   [`.vibekb/reference/PRODUCT.md`](.vibekb/reference/PRODUCT.md),
   [`.vibekb/reference/SCHEMA.md`](.vibekb/reference/SCHEMA.md),
   [`.vibekb/reference/MAINTENANCE.md`](.vibekb/reference/MAINTENANCE.md),
   [`.vibekb/reference/INITIALIZE.md`](.vibekb/reference/INITIALIZE.md),
   [`.vibekb/reference/INSTALLER.md`](.vibekb/reference/INSTALLER.md),
   [`.vibekb/reference/WORKFLOW.md`](.vibekb/reference/WORKFLOW.md), and
   [`.cursor/rules/vibekb.mdc`](.cursor/rules/vibekb.mdc).
   Short pointer: [`VIBEKB.md`](VIBEKB.md).

When the two rulebooks conflict on application code, Stoppr/`CLAUDE.md` wins.
VibeKB never changes Stoppr app behavior unless the owner explicitly asks.

## VibeKB product lock

**VibeKB exists so a vibe coder can open this project and understand what the
software is currently doing.** Do not turn it into a generic docs generator,
code browser, or AI activity log.

## Session start (VibeKB)

```bash
vibekb status
# or: php .vibekb/runtime/tools/vibekb.php status
```

## Required workflow for VibeKB changes

1. Read `.vibekb/work/handoff.md` and affected functionality records.
2. Trace claims against Stoppr source under `lib/` (README is not proof).
3. Update `.vibekb/` honestly (status + verification).
4. Keep Explainable Diagram topologies in lockstep with SVGs when diagrams
   change (`data-vibekb-node` / `data-vibekb-edge` markers).
5. Run `vibekb check` and
   `php .vibekb/runtime/tools/test-topology.php`.
6. If `/docs` is published, run `vibekb generate`
   (or `php .vibekb/runtime/tools/generate-static.php`).
7. Never expose secrets from `.env*`, key files, or CI config.

## Deployment for this repository

Public documentation deploys as **GitHub Pages `/docs`** (Mode B). Runtime
templates live under `.vibekb/runtime/guide/`; do not hand-edit
`docs/**/*.html`. This repo prefers `/docs` over `.vibekb/generated/` so Pages
can serve the snapshot.

## Upgrade / repair

- Upgrade runtime (keep model): `vibekb install --upgrade .`
- Migrate a pre-0.2.0 root layout: `vibekb migrate .`
- Repair empty scaffolding: `php .vibekb/runtime/tools/vibekb.php bootstrap`
- Do not use `--force` unless you intend to reset `.vibekb/`.

<!-- VIBEKB:START v1 -->
## VibeKB

This repository uses **VibeKB** to keep an honest, living explanation of what the
software currently does. VibeKB's knowledge base and its tooling live entirely
under [`.vibekb/`](./.vibekb/) — nothing else in this file is managed by VibeKB.

- **Orient:** `vibekb status` (or `php .vibekb/runtime/tools/vibekb.php status`)
- **Operating rules & lifecycle:** [`.vibekb/reference/WORKFLOW.md`](./.vibekb/reference/WORKFLOW.md)
- **Build or update the model:** follow [`.vibekb/prompts/INTEGRATE_VIBEKB.md`](./.vibekb/prompts/INTEGRATE_VIBEKB.md)
- **Before finishing a change:** `vibekb check` must be clean.

VibeKB owns only `.vibekb/` and this marked block. Everything outside the block
is yours; VibeKB never edits it.
<!-- VIBEKB:END -->
