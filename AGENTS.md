# AGENTS.md — Guidelines for AI agents (Stoppr + VibeKB)

This file applies to every coding agent working in this repository.

## Two rulebooks

1. **Stoppr application code** — follow [`CLAUDE.md`](CLAUDE.md) (Flutter/Dart)
   and [`.cursor/rules/flutter.mdc`](.cursor/rules/flutter.mdc).
   Do not modify application behavior unless the owner explicitly asks.
2. **VibeKB living software model** — follow [`PRODUCT.md`](PRODUCT.md),
   [`SCHEMA.md`](SCHEMA.md), [`MAINTENANCE.md`](MAINTENANCE.md),
   [`INITIALIZE.md`](INITIALIZE.md), [`INSTALLER.md`](INSTALLER.md), and
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
php tools/vibekb.php status
```

## Required workflow for VibeKB changes

1. Read `.vibekb/work/handoff.md` and affected functionality records.
2. Trace claims against Stoppr source under `lib/` (README is not proof).
3. Update `.vibekb/` honestly (status + verification).
4. Keep Explainable Diagram topologies in lockstep with SVGs when diagrams
   change (`data-vibekb-node` / `data-vibekb-edge` markers).
5. Run `php tools/vibekb.php check` and `php tools/test-topology.php`.
6. If `/docs` is published, run `php tools/vibekb.php generate`
   (or `php tools/generate-static.php`).
7. Never expose secrets from `.env*`, key files, or CI config.

## Deployment for this repository

Public documentation deploys as **GitHub Pages `/docs`** (Mode B). The `guide/`
directory is required so the static generator can render the same templates;
do not hand-edit `docs/**/*.html`.

## Upgrade / repair

- Upgrade runtime (keep model): `vibekb install --upgrade .`
- Repair empty scaffolding: `php tools/vibekb.php bootstrap`
- Do not use `--force` unless you intend to reset `.vibekb/`.
