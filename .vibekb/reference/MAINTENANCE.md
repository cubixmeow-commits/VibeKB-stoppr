# MAINTENANCE.md — Changing a feature with VibeKB

This is the complete workflow for a meaningful change. It keeps the living
software model accurate as the software evolves. Follow every step; skipping
verification or the model update is how VibeKB drifts.

VibeKB is **self-hosted**: the active `.vibekb/` model describes VibeKB itself, so
this workflow is what you follow when you change VibeKB — and it is the same
workflow an agent follows in any repository VibeKB is initialized in. The
[CLAUDE.md](./CLAUDE.md) lifecycle is the short version; this is the detailed one.

The `php tools/vibekb.php` CLI makes each step low-friction. Run
`php tools/vibekb.php help` to see every command.

> **Getting VibeKB in place.** This lifecycle assumes VibeKB is already installed
> in the repository. If it is not, run the installer (`php install.php`, see
> [INSTALLER.md](./INSTALLER.md)); to refresh the VibeKB runtime later, re-run it.
> If the `.vibekb/` workspace is ever missing or damaged,
> `php tools/vibekb.php bootstrap` recreates the scaffolding without touching your
> content. Neither the installer nor bootstrap builds the model — that is the
> agent work described below.

Worked example used throughout: **adding a `--json` output flag to
`vibekb status`** (a change to the `start-work-session` functionality).

## 1. Orient

```bash
php tools/vibekb.php status
```

Read the current functionality state, the handoff's next action, and the drift
summary. For the example, `start-work-session` is `verified-by-test` and lives in
`tools/vibekb.php`; note the active warnings (`model-can-drift-from-code`).

## 2. Record the requested change

Update `.vibekb/work/current.md`:
- requested outcome ("`vibekb status` can emit JSON for tooling"),
- current behaviour (human-readable text only),
- proposed behaviour (add `--json`; default stays text),
- affected functionality (`start-work-session`),
- expected files (`tools/vibekb.php`),
- data impact (none),
- risks (a second output path drifting from the text one),
- verification plan (run `status` and `status --json`; confirm both).

## 3. Implement

Make the code change in `tools/vibekb.php`. Respect the guardrails: PHP 8.2, no
new dependency, no build step, escape/encode output.

## 4. Identify affected functionality

```bash
php tools/vibekb.php affected --since <base-commit>
# or, for a single file:
php tools/vibekb.php affected tools/vibekb.php
```

This lists the functionality records, important-files entry, and diagram
topologies that reference each changed file — the knowledge that may now be
wrong. A changed file that maps to nothing is surfaced as "may need a new or
updated record," not ignored.

## 5. Verify

Exercise the real behaviour (`php tools/vibekb.php status` and the new
`--json`). Record what you tested and set the honest verification state. If you
only inferred something, say so — never upgrade a state you did not confirm (see
the `verification-must-reflect-evidence` warning).

## 6. Update the living model

- Update the affected functionality record(s) — for the example,
  `start-work-session`: describe the new `--json` behaviour and keep its
  verification state honest.
- If you added a behaviour, create its record and add it to
  `functionality/index.json`.
- Update `files/important-files.json` if a file's role changed.
- If the change alters what a diagram shows, update the diagram record **and its
  SVG**; for an **explainable** diagram keep the topology in lockstep (node
  purposes, edge mechanisms/explanations, per-file reasons, verified/inferred
  states) and keep the SVG's `data-vibekb-node` / `data-vibekb-edge` markers
  mapped to the topology ids in both directions. Never mark an inferred edge
  verified; delete an edge whose mechanism you can no longer state.

## 7. Update repository memory

Add only meaningful records, each linked to functionality:
- a `change` record (before/after/impact),
- a `decision` if you chose between real alternatives,
- a `warning` or `discovery` if you learned something risky.

Do not log every edit.

## 8. Refresh provenance (if you re-verified against source)

If your work re-verified the model against the current code, update the
`manifest.json` `provenance` block (`source_commit`, `last_verified`,
`verification_scope`). Never imply the guide auto-updates.

## 9. Update the handoff

Update `.vibekb/work/handoff.md`: current functionality state, completed work,
verification done, what is still open, active warnings, and the **exact** next
recommended action. Clear or refresh `.vibekb/work/current.md`.

## 10. Check and regenerate

```bash
php tools/vibekb.php check          # validate + broken refs + drift + /docs sync
php tools/vibekb.php generate       # rebuild /docs from .vibekb/
php tools/test-topology.php         # explainable-diagram contract still holds
```

`check` must report no definite errors. `/docs` is **generated output** — never
hand-edit it; regenerate and commit it alongside the model change. The generator
refuses to build if the model has validation errors.

## Quick reference

```bash
php tools/vibekb.php status                     # session start
php tools/vibekb.php affected --since <ref>      # impact of your changes
php tools/vibekb.php check [--strict]            # consistency gate
php tools/vibekb.php generate                    # refresh /docs
find guide tools -name '*.php' -print0 | xargs -0 -n1 php -l   # syntax
```

The dynamic guide's **Reference** view shows the same validation issues as
`check`; in development a banner links to them (`VIBEKB_DEV=1`).

## Changing the Go developer CLI

The `vibekb` binary (`cmd/vibekb`, `internal/*`) is part of VibeKB's own software,
so a change to it follows this same lifecycle — including updating the
`run-the-developer-cli` functionality record and regenerating `/docs`. Its own
checks are:

```bash
go build ./... && go vet ./... && go test ./...   # the vibekb CLI
gofmt -l cmd internal                              # must print nothing
```

Keep the honesty boundary: the CLI **delegates** every model-semantic command to
the PHP core and must never grow a second model loader, template system, or
generator. Its native surface is the parts that don't interpret the model —
diagnostics and installation. `vibekb install` is native (`internal/installer` +
`embed.go`); it scaffolds from the canonical `template/starter/` data, the same
definition `tools/lib/Starter.php` reads for `bootstrap`. Change starter content in
`template/starter/`, not in code, so both stay in sync. See `ARCHITECTURE.md`.
