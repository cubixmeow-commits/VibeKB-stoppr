# VibeKB — Product Definition

> **VibeKB exists so a vibe coder can open a software project at any point in
> its life and understand what the software is currently doing.**

## The promise

# Understand what your software is doing.

VibeKB gives AI-assisted developers a living explanation of their application's
current functionality, how it works, what AI is changing, and why.

Everything else in VibeKB exists to support that outcome.

## The target user

Someone who builds software with coding agents — Claude Code, Cursor, Codex,
Windsurf, GitHub Copilot, Gemini CLI, and others. They can install things, run
commands, use GitHub, and ship. Their problem is **not** that they can't write
code. It's that AI can build and modify software faster than they can maintain
an accurate mental model of it.

They say things like:

- "I know the app works, but I don't understand how."
- "Claude changed six files and I don't know why."
- "I don't know which files matter."
- "The AI says it's done, but I can't verify it."
- "I no longer know what functionality is actually implemented."

VibeKB should make this person **more capable and less dependent.**

## The central object: the Living Software Model

The living software model is the current, understandable model of the
application. It explains:

1. **What the software is** — purpose, users, outcome, scope, non-goals.
2. **What it currently does** — implemented, partial, planned, experimental,
   disabled, deprecated, broken, unknown functionality.
3. **How it works** — components, entry points, request/data flows, storage,
   configuration, deployment.
4. **Where functionality lives** — the files, routes, and stores that
   implement each behaviour.
5. **What AI is doing** — the current objective, plan, affected functionality,
   files, risks, and progress.
6. **Why it works this way** — decisions, constraints, assumptions, warnings,
   discoveries.
7. **What changed** — meaningful behavioural changes and their impact.
8. **What the next human or AI must know** — the handoff.

## Functionality is the primary unit

VibeKB is **not** organized around files, decisions, or AI sessions. It is
organized around **software functionality** — the things the software does from
a user's or system's point of view (create an account, save an idea, export
data, initialize the database…).

A functionality record is readable without opening the source. Code references
support the explanation; they don't replace it.

## Repository memory supports functionality

Repository memory — decisions, constraints, assumptions, warnings,
discoveries, changes, sessions, handoffs — is valuable because it keeps the
explanation of functionality **accurate and resistant to drift**. Every memory
record connects back to functionality, files, data, components, or active
work. Memory does not replace software understanding; it protects it.

## Intended, implemented, and verified are different things

VibeKB must always let the user distinguish:

- **Intended** behaviour (what someone meant to build),
- **Implemented** behaviour (what the code does),
- **Verified** behaviour (what has actually been confirmed, and how).

Every record carries a **status** and a **verification / provenance** state.
Do not present a generated file as working. Do not present an AI claim as
evidence. Do not hide uncertainty.

## The two product tests

Before adding any page, record, or feature, ask:

1. **"Does this help a vibe coder understand what the software is doing right
   now?"** If no, it does not belong in V1.
2. **"Does this help keep that explanation accurate as the software changes?"**
   If yes, it may belong as supporting repository memory.

## V1 scope

The working V1 is a repository-owned content model (`.vibekb/`) rendered through
one template set in two output modes over the same source: a **dynamic PHP
guide** (`guide/`) and a **static snapshot** (`/docs`, via
`tools/generate-static.php`) for GitHub Pages and any static host. Views:
Software Overview, Functionality Index, Functionality Detail, How It Works,
Diagrams, Data & Storage, Files That Matter, Current AI Work, Changes, Why It
Works This Way, AI Handoff, Reference, and Search.

Every rendering carries **objective provenance** — the source commit analysed,
when the analysis was generated, the verification scope, and that the output
does not update itself. **Diagrams** are first-class, source-grounded records,
and can be **explainable** (a repository-owned topology of nodes, edges,
mechanisms, and files-with-reasons — see *Explainable Diagrams* below).
The static snapshot is generated output; `.vibekb/` remains the source of
truth. None of this changes the promise: it exists to help a vibe coder
understand what the software is doing right now.

VibeKB is **self-hosted**: the active `.vibekb/` describes VibeKB itself, and a
repository-owned self-maintenance CLI (`tools/vibekb.php`) helps a coding agent
run the maintenance lifecycle and detect drift between the code and the model —
honestly separating what is *detected* mechanically from what an agent must
*interpret*. Bundled models of other applications live under `examples/` and are
never the active model.

## Explainable Diagrams

Diagrams in VibeKB are **explainable**, not decorative. A diagram is a visual
projection of the living software model, and it must teach how the software
works *before* the reader clicks anything: labelled nodes and mechanism-labelled
edges that read like a sentence (Login Form → *submits-to* → Login Controller →
*delegates-to* → Authentication Service). Selecting a node or edge then reveals
the full explanation.

Every click answers the reader's next question. Everything must justify its own
existence:

- **Nodes** answer: *What is this?* — a concise title and a plain-language
  purpose. A node is a software concept, never a bare filename, and it must
  explain why it appears.
- **Edges** answer: *Why are these connected?* — a concrete mechanism from a
  controlled vocabulary and a one-sentence explanation. The mechanism is the
  visual proof the relationship passed the explainability gate. Shared naming,
  shared folders, and overlapping vocabulary are **not** mechanisms; an edge
  with no stateable mechanism is omitted, not drawn.
- **Files** answer: *Where is this implemented, and why should I read it?* — a
  curated list, each file with a role and a reason. A file with a reason is
  knowledge; a bare file path is browsing.
- **Folders** answer: *Where does it live?* — a compact repository location, not
  a navigable directory tree.
- **External source links** answer: *Show me the implementation.* — the terminal
  handoff. VibeKB never renders the source itself; the code lives at the end of
  a question path (Architecture → Component → Purpose → Location → File & reason
  → External source link).

**Verification communicates how well a mechanism is grounded, not whether an
unexplained edge is allowed.** A verified edge (traced in source) renders as a
solid line; an inferred edge (supported by structure, DI, imports, routing,
configuration, or naming plus context, but not fully traced) renders dashed and
must state its basis. Line style and colour are never the only signal — the
state is always stated in text. There is no speculative tier: a missing
unsupported edge is better than a plausible-looking false one, and a documented
knowledge gap is better than a fabricated explanation. The topology is
repository-owned JSON validated against the same content model; it adds only the
graph-specific knowledge and reuses everything else the model already knows.

## Non-goals

VibeKB is **not** a documentation generator, a repository-memory archive, a
code browser, or an AI activity log. It is a **living explanation of the
software's current functionality.**

V1 deliberately does not require: authentication, a database, an external/AI
API, embeddings, a vector store, background workers, or a JavaScript build
step.

## Success criteria

A user can accurately summarise VibeKB as:

> "VibeKB shows me what my software is doing, how the functionality works, what
> AI is changing, and why."
