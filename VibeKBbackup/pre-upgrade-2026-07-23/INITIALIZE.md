# INITIALIZE.md — Adding VibeKB to another repository

This is the reproducible process for an AI agent to initialize VibeKB in a real
project and produce a **living software model** that explains what the
application currently does — honestly separating intended, implemented, and
verified behaviour. Followed carefully, it reproduces the quality demonstrated
by the StopPR field test (see `docs/STOPPR_INTEGRATION_AUDIT.md`).

Copy `guide/` (the app + renderer), `tools/` (the static generator + the
validator), `PRODUCT.md`, `CLAUDE.md`/`AGENTS.md`, and `SCHEMA.md` into the
target repo, then build a fresh `.vibekb/` following the steps below. A
project-agnostic prompt that drives this workflow lives at
[`prompts/INTEGRATE_VIBEKB.md`](./prompts/INTEGRATE_VIBEKB.md).

> **Never modify the target application's code** to initialize VibeKB, unless
> the owner explicitly asks. VibeKB reads the app; it does not change it.

## The workflow

Follow every step. Steps 1–13 build and validate the model; 14–17 produce and
verify the chosen output.

1. **Read the target repository's agent instructions** (`CLAUDE.md`,
   `AGENTS.md`, `.cursor/rules`, `README`) before anything else. Respect them.
2. **Inventory the repository without modifying application code.** Identify the
   language, framework, entry points, how it runs, where data is stored, and
   configuration/deployment. Do not treat the README as proof of behaviour.
3. **Identify functional areas** — the grouped product categories
   (`functionality/index.json` groups), e.g. onboarding, billing, admin.
4. **Trace individual functionality from source.** Walk each real behaviour from
   trigger to result and note the files that implement it. Functionality — not
   files — is the primary unit.
5. **Distinguish implemented, partial, planned, broken, and unknown** behaviour.
   Never describe planned functionality as implemented.
6. **Record verification states honestly:** `verified-by-test`,
   `verified-manually`, `verified-from-source`, `inferred-from-source`,
   `reported-by-developer`, or `not-verified`. Never upgrade a state you did not
   confirm.
7. **Identify files that matter** — only the files worth understanding, each
   with an evidence-based safety level and provenance.
8. **Map data and storage** — databases, files, caches, sessions; what each
   behaviour reads and writes.
9. **Identify external services** — third-party APIs/SDKs and the impact if they
   fail or are misconfigured.
10. **Capture active warnings and placeholders** — real landmines: known
    defects, placeholder configuration, unverified integrations, deployment and
    security-sensitive areas. Give each an affected functionality, severity, and
    a safe next action.
11. **Create a current-work record and a handoff** (`work/current.md`,
    `work/handoff.md`) — objective, with an explicit next recommended action.
12. **Create only source-grounded diagrams, and make them explainable.** Add a
    small, accurate set under `.vibekb/diagrams/` (records + repository-owned
    SVGs). Every SVG needs an accessible `<title>` and `<desc>`; label any
    inferred or unverified path in the diagram itself. Do not generate arbitrary
    diagrams to hit a number. For each diagram, author a topology
    (`diagrams/topology/<id>.json`) so it teaches how the software works before
    any click:
    1. Identify a small set of important software concepts (nodes) — concepts,
       not filenames. Give each a concise title and a plain-language purpose.
    2. Identify only meaningful relationships (edges). **Do not draw an edge
       unless you can state a concrete mechanism explaining why the source and
       target are connected.** "Both touch users", "both relate to
       authentication", shared naming, shared folders, and overlapping
       vocabulary are **not** mechanisms.
    3. Assign each edge a mechanism from the controlled vocabulary and write one
       sentence explaining it.
    4. Classify each node and edge honestly as verified (traced in source) or
       inferred (defensible from structure/DI/imports/routing/config/naming +
       context). Do not mark something verified merely because the AI generated
       it. There is no "hypothesized" tier — omit an edge you cannot defend and
       record the gap in the narrative/uncertainty.
    5. Attach only relevant files, each with a role and a one-sentence reason
       for why it matters; show the repository location.
    6. Connect the SVG to the topology: mark each node group with
       `data-vibekb-node="<id>"` and each edge group with
       `data-vibekb-edge="<id>"`, and link markers to the `#node-<id>` /
       `#edge-<id>` anchors so the diagram is usable without JavaScript.
    7. Quality test: read the visible nodes and edge labels as a sentence — the
       diagram should teach how the software works before any selection.
13. **Validate the `.vibekb/` model** with `php tools/validate.php` (and
    `php tools/test-topology.php`). Resolve every error (duplicate ids, missing
    references, invalid statuses/verification states, missing diagram assets,
    diagrams lacking title/description, topology contract violations,
    out-of-vocabulary edge mechanisms, files without reasons, SVG markers that
    do not map to the topology, contradictory totals).
14. **Choose and record provenance** in `manifest.json` (`provenance` block):
    source repository, branch, the **commit analyzed**, verification scope, last
    verified, and `updates_automatically: false`. Never imply auto-freshness.
15. **Generate the selected output mode:**
    - **Mode A (dynamic PHP guide):** deploy `guide/`; it reads `.vibekb/` live.
    - **Mode B (static snapshot):** run `php tools/generate-static.php` to build
      `/docs` for GitHub Pages or any static host.
    - Or both — they share one `.vibekb/` source and one template set.
16. **Verify internal links and asset paths.** For static output, confirm
    `/docs/index.html` loads, links work under a repository subpath, assets are
    relative, SVGs are valid XML with title/desc, and search entries resolve.
17. **Record the source commit and generation timestamp**, then **confirm no
    application code was modified** unless explicitly requested.

## Mark uncertainty and ask only essential questions

Where you cannot verify something from the source, mark it `unknown` /
`not-verified` and add an assumption record with a `next_check`. Ask the owner
**only** what you cannot answer from the code and that materially changes the
model (e.g. "is this half-built feature meant to ship or be removed?").

## What "done" looks like

Someone can open the guide (dynamic or the static `/docs` snapshot), read the
overview, and correctly understand what the application does right now —
including what is unfinished, risky, or unverified — with a provenance block
that states exactly which commit was analysed and that the snapshot does not
update itself.
