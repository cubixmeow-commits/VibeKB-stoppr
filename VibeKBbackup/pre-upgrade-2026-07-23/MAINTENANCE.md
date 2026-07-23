# MAINTENANCE.md — Changing a feature with VibeKB

This is the complete workflow for a meaningful change. It keeps the living
software model accurate as the software evolves. Follow every step; skipping
verification or the model update is how VibeKB drifts.

The current example model is **SousMeow** — a real application derived read-only
from `cubixmeow-commits/dev-portfolio-v2` (`projects/sousmeow`). Because the
example is a real, separate app, **any change to a functionality claim must be
re-verified against the SousMeow source first.**

Worked example: **promoting `manage-account` from `inferred-from-source` to
`verified-from-source`.**

## 1. Describe current behavior

Read the affected functionality record(s) and be able to state what the software
does today. For the example: `manage-account` is currently
`inferred-from-source` — the routes and views confirm the surface, but
`app/Controllers/AccountController.php` has not been traced line by line.

## 2. Record the requested change

Update `.vibekb/work/current.md`:
- requested outcome ("verify the account settings flows against source"),
- current behaviour (inferred),
- proposed behaviour (verified, or corrected if the trace disagrees),
- affected functionality (`manage-account`),
- expected files (`AccountController.php`, `AccountDataExport.php`),
- data impact (none — a verification pass),
- risks (the trace may reveal the record overstates behaviour),
- verification plan (read the controller; exercise the flows if runnable).

## 3. Identify affected functionality

List every record the change touches and check `depends_on` / derived
dependents. For a real code change (not just verification), check active
warnings — e.g. `read-write-path-coupling` for anything touching Pantry or
Artifact fields.

## 4. Implement

For SousMeow this usually means **reading the source**, not editing it — VibeKB
never modifies the example app. If you are documenting a real code change the
SousMeow team made, respect its constraints (PHP 8, `public/` root, two schema
dialects) and warnings.

## 5. Verify

Trace the actual source (and exercise the behaviour where a local SousMeow
checkout is runnable). Record what you read/tested. Set the honest verification
state (`verified-from-source`, `verified-manually`, etc.). If something is still
untested, say so — do not upgrade a state you did not confirm.

## 6. Update functionality records

- Update `manage-account`: change `verification` to `verified-from-source`
  **only if** you actually traced `AccountController`; fix the body if the trace
  disagreed.
- If you discovered a new behaviour, create its record and add it to
  `functionality/index.json`.
- Update `files/important-files.json` provenance for any file you traced.

## 7. Update repository memory

Add only meaningful records:
- a `change` record (before/after/impact) linked to the functionality,
- a `decision` if you chose between real alternatives,
- a `warning` or `discovery` if you learned something risky.
Do not log every edit.

## 7a. Update diagrams if the picture changed

If a behaviour change alters something a diagram shows (a flow, a storage map,
a risk), update the affected `.vibekb/diagrams/` record **and its SVG**. Keep
the SVG accessible (`<title>` + `<desc>`) and keep inferred/unverified paths
visibly labelled. Diagrams must never claim behaviour not traced to source; set
the diagram's `verification` and `last_verified` honestly. Do not add diagrams
you cannot ground in source.

If the diagram is **explainable** (has a `diagrams/topology/<id>.json`), keep the
topology in lockstep: update node purposes, edge mechanisms and explanations,
per-file reasons, and verified/inferred states to match the new behaviour, and
keep the SVG's `data-vibekb-node` / `data-vibekb-edge` markers mapped to the
topology ids. Never mark an inferred edge verified, and never keep an edge whose
mechanism you can no longer state — delete it and record the gap. Re-run
`php tools/validate.php` and `php tools/test-topology.php`.

## 7b. Refresh provenance

If you re-verified against source, update the `manifest.json` `provenance` block
(`source_commit`, `last_verified`, `verification_scope`). Never imply the guide
auto-updates.

## 8. Update the handoff

Update `.vibekb/work/handoff.md`: current functionality state, completed work,
verification done, what's still open, active warnings, and the exact next
recommended action. Clear or update `.vibekb/work/current.md` when the work is
done.

## 8a. Regenerate the static snapshot (if `/docs` is published)

If this repository publishes the static snapshot, regenerate it so the
published guide reflects the model change:

```bash
php tools/generate-static.php   # rebuilds /docs from .vibekb/
```

`/docs` is **generated output**, not the source of truth. The generator refuses
to build if the model has validation errors.

## After every change: run the checks

```bash
# Syntax
find guide tools -name '*.php' -print0 | xargs -0 -n1 php -l

# Headless content validation (CI-friendly; exits non-zero on errors)
php tools/validate.php

# Load the guide and read the Reference view's validation section
VIBEKB_DEV=1 php -S 127.0.0.1:8080 -t .
# open http://127.0.0.1:8080/guide/?view=reference
```

`php tools/validate.php` and the Reference view must show no validation errors.
Every relationship you added must resolve (no ⚠ broken chips), every diagram SVG
must be valid XML with a `<title>`/`<desc>`, every explainable topology must pass
the contract (unique ids, resolvable edges, controlled mechanisms, files with
reasons, SVG markers mapped both ways), and every total must state its unit.
