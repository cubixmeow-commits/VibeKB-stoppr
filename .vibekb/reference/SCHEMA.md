# SCHEMA.md — VibeKB content model

All VibeKB content lives in `.vibekb/` as human-readable, AI-editable files:
Markdown with front matter for records, and small JSON manifests for indexes.
No database is required. The loader (`guide/lib/Content.php`) parses, resolves
relationships, and validates this content; the Reference view shows live
diagnostics.

## Directory layout

```
.vibekb/
├── manifest.json                 # content-model version + example metadata
├── project/
│   ├── identity.md               # what the software is
│   ├── intent.md                 # why it exists / what it must not become
│   ├── current-state.md          # what it does right now
│   └── constraints.md            # boundaries overview
├── functionality/
│   ├── index.json                # group definitions + display order
│   └── records/*.md              # one file per functionality (the primary unit)
├── system/
│   ├── mental-model.md
│   ├── components.md
│   ├── request-flow.md
│   ├── data-flow.md
│   ├── storage.md
│   └── deployment.md
├── files/
│   └── important-files.json      # curated important files
├── diagrams/
│   ├── index.json                # diagram group definitions + display order
│   ├── records/*.md              # one file per diagram (record + metadata)
│   ├── assets/*.svg              # repository-owned SVGs (accessible title+desc)
│   └── topology/*.json           # optional explainable topology per diagram
│                                 #   (nodes, edges, mechanisms, files+reasons)
├── memory/
│   ├── decisions/*.md
│   ├── constraints/*.md
│   ├── assumptions/*.md
│   ├── warnings/*.md
│   ├── discoveries/*.md
│   └── changes/*.md
└── work/
    ├── current.md                # current AI work
    ├── handoff.md                # current handoff
    └── sessions/*.md             # AI work sessions
```

## Front matter conventions

Records are Markdown files that begin with a `---` fenced front-matter block.
Supported value forms: scalars (`key: value`), quoted strings, booleans,
integers, inline lists (`key: [a, b, c]`), and block lists (`- item` on the
following lines). The Markdown body holds the human narrative.

## Shared base fields

| Field | Meaning |
|-------|---------|
| `id` | Stable, unique id (lowercase, digits, hyphens). Defaults to filename. |
| `type` | Record type (see below). Defaults from the directory. |
| `title` | Human title. |
| `summary` | One-sentence plain-language summary. |
| `status` | Lifecycle/verification status (per type). |
| `verification` | Provenance state (see below). |
| `created`, `updated` | ISO dates. |
| `tags` | List of tags. |
| `functionality` | List of functionality ids this record relates to. |
| `files` | List of file paths involved. |

## Record types

### functionality (`functionality/records/*.md`) — the primary unit
Front matter: `id, title, summary, area, status, verification, user_facing,
trigger, files, reads, writes, config, depends_on, related_memory, created,
updated, tags`. Body sections (A–Q): In one sentence · User experience ·
Current behavior · Step-by-step flow · Implementation map · Data used ·
Dependencies · Dependents · Failure cases · Configuration · Current state ·
Safe to change · Use caution · Why it works this way · Change history · Current
AI work · Related functionality.

- `area` must match a group id in `functionality/index.json` (unknown areas
  fall into an "Other" group).
- `depends_on` lists other functionality ids; **dependents are derived**
  (reverse links) automatically.
- `related_memory` uses `type:id` references, e.g. `decision:never-calls-ai`.

### project (`project/*.md`)
Identity, intent, current-state, constraints documents. Free-form body with a
`summary` in front matter.

### system (`system/*.md`)
Mental model, components, flows, storage, deployment. `title`, `summary`, body.

### file (`files/important-files.json`)
JSON objects: `path, purpose, functionality[], runs_when, depended_on_by[],
depends_on[], safety, test_after_change, provenance`.

### diagram (`diagrams/records/*.md`)
Source-grounded SVG maps of how the software works. Front matter: `id, title,
summary, diagram_type, group, svg, topology, functionality[], files[], data[],
warnings[], diagrams[], status, verification, provenance, last_verified,
uncertainty, created, updated`. The Markdown body explains what the viewer is
seeing ("What am I looking at?", "Why it matters", "What is uncertain").

- `svg` is a filename in `diagrams/assets/`; the SVG **must** be well-formed XML
  with an accessible `<title>` and `<desc>`, and is rendered inline.
- `topology` (optional) is a filename in `diagrams/topology/` that upgrades the
  diagram into an **explainable diagram** (see below). Diagrams without it keep
  rendering as a picture + narrative.
- `group` must match a group id in `diagrams/index.json` (unknown groups fall
  into an "Other" group).
- `functionality[]`, `warnings[]`, and `diagrams[]` are back-links resolved and
  validated against functionality records, memory warnings, and other diagrams.
- `diagram_type` is one of the supported types (application-overview,
  user-journey, startup-flow, authentication-flow, access-flow, navigation-map,
  feature-access, request-flow, data-flow, storage-map, external-services,
  code-architecture, state-management, risk-and-uncertainty-map). These are
  *supported* types, not mandatory diagrams — a repository ships only the
  diagrams it can ground in source, and must label inferred or unverified paths
  in the diagram itself.

### explainable-diagram topology (`diagrams/topology/<diagram-id>.json`)
An **explainable diagram** is a visual projection of the living software model:
every node states what it is, every edge states the concrete mechanism
connecting its endpoints, every displayed file states why it matters, and the
terminal handoff is an external source link. The topology adds only the
graph-specific knowledge (nodes, edges, mechanisms, per-node/edge file roles and
reasons, repository locations) that does not already live in functionality
records, `important-files.json`, memory, or provenance — those are **reused and
resolved**, never duplicated.

JSON, so the limited front-matter parser is never asked to hold a nested graph.
No YAML, no database, no build step. Shape:

```json
{
  "version": 1,
  "nodes": [
    {
      "id": "login-controller",
      "title": "Login Controller",
      "purpose": "Validates login requests and delegates authentication.",
      "location": "app/Controllers",
      "functionality": ["user-login"],
      "warnings": [],
      "files": [
        { "path": "app/Controllers/LoginController.php",
          "role": "primary implementation",
          "reason": "Receives and validates the login request." }
      ],
      "verification": "verified-from-source",
      "uncertainty": ""
    }
  ],
  "edges": [
    {
      "id": "login-controller-to-auth-service",
      "from": "login-controller",
      "to": "auth-service",
      "mechanism": "delegates-to",
      "label": "delegates authentication",
      "explanation": "The login controller validates the request and delegates credential checking to the authentication service.",
      "functionality": ["user-login"],
      "warnings": [],
      "files": [
        { "path": "app/Services/AuthService.php", "role": "callee",
          "reason": "Performs credential validation." }
      ],
      "verification": "inferred-from-source",
      "basis": "The controller receives the service via DI and exposes a login action that delegates credential handling.",
      "uncertainty": "The exact runtime call site was not traced."
    }
  ]
}
```

Rules (enforced by the loader and `tools/validate.php`):

- `version` must be a supported schema version (currently `1`).
- Node ids and edge ids are each unique within the diagram.
- Every node needs a `title` and a `purpose` (nodes answer *what is this?*).
- Every edge needs `from`, `to`, a `mechanism`, and a one-sentence `explanation`
  (edges answer *why are these connected?*). `from`/`to` must resolve to nodes
  in the same topology.
- `mechanism` must be a value from the **controlled edge-mechanism vocabulary**
  (below). Vague pseudo-mechanisms are rejected.
- `verification` uses the shared verification vocabulary. V1 renders two states:
  **verified** (`verified-*`) as a solid line and **inferred**
  (`inferred-from-source`, or any non-verified state) as a dashed line. Line
  style and colour are never the only signal — the state is stated in text too.
  There is no "hypothesized" tier: if a mechanism cannot be named and defended,
  the edge is omitted and the gap is recorded in the narrative/uncertainty.
- Every displayed file must be a safe **repository-relative** path with a
  non-empty `reason`. `role` should come from the file-role vocabulary. A file's
  canonical purpose/safety are pulled from `important-files.json` when it is a
  known important file; the topology `reason` is the diagram-specific context.
- `functionality[]` and `warnings[]` on nodes/edges must resolve.
- The SVG must mark each node with `data-vibekb-node="<node-id>"` and each edge
  with `data-vibekb-edge="<edge-id>"`; the topology ids and SVG markers must map
  **both ways** (no orphan markers, no unmarked nodes/edges). The SVG stays
  valid XML with an accessible `<title>`/`<desc>`; markers link to the
  `#node-<id>` / `#edge-<id>` explanation anchors so a no-JavaScript reader can
  follow any element to its written explanation.
- Malformed topology is reported as a Reference diagnostic and fails
  `tools/validate.php`; it never crashes the guide, and a diagram with no
  topology still renders.

**Controlled edge-mechanism vocabulary** (single source: `edge_mechanism_vocabulary()`
in `guide/lib/helpers.php`): `calls`, `delegates-to`, `reads`, `writes`,
`configures`, `instantiates`, `depends-on`, `emits`, `listens-to`, `validates`,
`stores-in`, `retrieves-from`, `creates`, `updates`, `deletes`, `routes-to`,
`renders`, `returns-to`, `sends-to`, `receives-from`. Vague mechanisms
(`relates-to`, `works-with`, `interacts-with`, `associated-with`,
`connected-to`, and bare `uses`) are **not** permitted.

**File-role vocabulary** (`file_role_vocabulary()`): `primary implementation`,
`entry point`, `caller`, `callee`, `dependency`, `configuration`, `data model`,
`storage`, `renderer`, `route definition`, `validation`, `integration adapter`,
`supporting utility`, `test or verification evidence`.

### memory records (`memory/<type>/*.md`)
`decision, constraint, assumption, warning, discovery, change`. Each links back
via `functionality` and/or `files`. Type-specific fields:
- **decision**: `alternatives`, plus body Context/Decision/Reason/Consequences.
- **constraint**: `status` (active), source and consequences in body.
- **assumption**: `confidence`, `verification`, `invalidated_by`, `next_check`.
- **warning**: `severity` (critical/high/medium/low).
- **discovery**: evidence + `changed_model` in body/front matter.
- **change**: before/after/impact in body; may link a `session`.

### work (`work/*.md`)
- **current** (`current.md`): `objective, status, verification_state,
  affected_functionality, expected_files, data_impact, risks` + body.
- **handoff** (`handoff.md`): `verification_state` + body.
- **session** (`sessions/*.md`): `date, verification, functionality, files,
  change` + body.

## Controlled vocabularies

**Statuses** (functionality): `implemented`, `partial`, `planned`,
`experimental`, `disabled`, `deprecated`, `broken`, `unknown`,
`needs-verification`.

**Verification / provenance**: `verified-by-test`, `verified-manually`,
`verified-from-source`, `inferred-from-source`, `reported-by-developer`,
`not-verified`, `verification-failed`, `superseded`, `contradicted`.

**File safety levels**: `presentation-only`, `low-impact`, `moderate-impact`,
`understand-dependencies-first`, `high-impact`, `generated-or-managed`,
`unknown`.

**Warning severity**: `critical`, `high`, `medium`, `low`.

## Manifest provenance & generation metadata

`manifest.json` carries a `provenance` block describing the **source** the guide
explains — `name`, `source_repository`, `source_subpath`, `source_branch`,
`source_commit`, `analyzed`, `verification_scope`, `last_verified`,
`updates_automatically` (always `false` unless an actual update mechanism is
implemented and verified), and a `freshness_note`.

The **generation** event (mode, generated time, generator commit/branch) is
supplied at render time — `dynamic` for the live PHP guide, `static` for a
`/docs` snapshot — and is never conflated with the source provenance. The guide
renders both through the shared provenance component (`guide/lib/Provenance.php`)
using objective labels (*Source commit analyzed*, *Analysis generated*,
*Work-record status*, *Last verified against source*), never undefined ones like
"Last meaningful update".

## Count vocabulary

Three counts must never be conflated, and every displayed total states its unit:
**functional areas** (grouped categories), **functionality records** (individual
behaviours), and **status counts** (records by status). Totals are derived from
the records themselves so they cannot silently contradict each other.

## Validation rules (enforced by the loader and `tools/validate.php`)

- Duplicate `id`s within a type are reported (functionality and diagrams).
- Functionality must have `id`, `title`, `status`, `summary`.
- `status`, `verification`, and file `safety` must be in the vocabularies.
- `depends_on` must point to existing functionality.
- `related_memory` and memory `functionality` back-links must resolve.
- File `functionality` links must resolve.
- Diagrams must have `id`, `title`, `svg`; a valid `verification`; an SVG asset
  that exists, is well-formed XML, and has an accessible `<title>` (and
  ideally `<desc>`); and resolvable `functionality`, `warnings`, and `diagrams`
  links.
- A diagram's optional `topology` file must exist, be valid JSON of a supported
  schema version, have unique node/edge ids, resolvable edge endpoints,
  controlled mechanisms, valid verification states, resolvable
  functionality/warning references, safe repository-relative file paths each
  with a non-empty reason, and SVG markers that map to the topology ids in both
  directions. A diagram with no topology is reported only as a non-fatal
  compatibility warning.
- `tools/validate.php` additionally checks provenance completeness, that
  area/record/status totals reconcile, and — when a `/docs` snapshot exists —
  that its search entries point at pages that exist. It exits non-zero on error.
- Malformed JSON and unreadable files are reported, not fatal.

Issues appear in the **Reference** view; in development a banner links to them.
`php tools/validate.php` reports the same set headlessly for CI.
