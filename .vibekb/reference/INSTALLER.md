# INSTALLER.md — Installing, upgrading, and repairing VibeKB

VibeKB ships with a first-class installer so adopting it into a repository is a
command, not a manual copy. This document explains the installer end to end:
installation, upgrades, repairing a damaged workspace, the template structure,
and where to extend it.

> **The one thing to remember:** the installer *prepares the workspace*; an AI
> coding agent *interprets the software*. The installer never analyses,
> understands, or documents your application — it lays down an empty, valid
> VibeKB workspace and hands off to your agent. That separation is deliberate and
> is what keeps VibeKB honest.

## Quick start

Install the `vibekb` CLI from the website (macOS/Linux), then install into your
project:

```bash
curl -fsSL https://iainreid.dev/vibekb/install.sh | sh
vibekb install /path/to/your/project
```

Prefer to install the binary manually? Download from
[GitHub Releases](https://github.com/cubixmeow-commits/VibeKB/releases/latest),
rename to `vibekb` (or `vibekb.exe` on Windows), put it on your `PATH`, and run
the same `vibekb install` command.

Then open your project in a coding agent (Claude Code, Cursor, Codex, …) and ask
it to **build the first VibeKB model using
`.vibekb/prompts/INTEGRATE_VIBEKB.md`**.

Requirements: network access for the website installer (or a release binary / local
build). **PHP 8.2+ is required only to *run* the installed guide**
(`php .vibekb/runtime/tools/vibekb.php …`, the dynamic app under
`.vibekb/runtime/guide/`), never to install. The website installer supports macOS
and Linux; Windows binaries are available from Releases.

> **How it works.** The website script (`install.sh`) downloads the matching
> release asset and places `vibekb` on your `PATH`. The `vibekb` binary embeds the
> installer payload and a canonical starter definition (`template/starter/`), so
> `vibekb install` copies files and scaffolds a fresh `.vibekb/` **without
> launching PHP** and without the source clone needing to remain on disk. The set
> of installed files is declared in
> [`template/manifest.json`](./template/manifest.json), which the binary parses
> directly — the single source of truth.
>
> **Legacy `php install.php`.** The old entry point still works: it is now a thin
> compatibility wrapper that forwards to `vibekb install` (or prints how to get
> the binary). There is only one repository installer implementation — the Go one.

### Advanced: build from source

```bash
git clone https://github.com/cubixmeow-commits/VibeKB.git
cd VibeKB
go build -o vibekb ./cmd/vibekb           # Go 1.24+
./vibekb install /path/to/your/project
```

Publishing new binaries is documented in [RELEASE.md](./RELEASE.md). Homebrew and
Winget are a later milestone; the website `curl | sh` installer is the primary
end-user path.

## What gets installed

Everything VibeKB owns is written **under `.vibekb/`** — nothing lands at your
repository root. See [docs/REPOSITORY_SAFETY.md](./docs/REPOSITORY_SAFETY.md) for
the full ownership model and the safety guarantee.

| Where | What | Who owns it |
|-------|------|-------------|
| `.vibekb/runtime/` (`guide/`, `tools/`, `template/starter/`) | The **installed runtime** — the app, the CLI, and the starter definition `bootstrap` repairs from | VibeKB (safe to refresh on upgrade) |
| `.vibekb/reference/`, `.vibekb/prompts/` | VibeKB's operating docs and the integration prompt | VibeKB |
| `.vibekb/` (root: `project/`, `functionality/`, …) | The **living software model** — your project's knowledge | Your project (never overwritten on upgrade) |
| `.vibekb/generated/` | The **generated static snapshot** (`vibekb generate` / `php .vibekb/runtime/tools/vibekb.php generate`) | Generated output |
| `.cursor/rules/vibekb.mdc`, `.github/instructions/vibekb.instructions.md` | Optional **namespaced adapters** — added only when that tool is already in use | VibeKB (namespaced) |
| `AGENTS.md` / `CLAUDE.md` (managed block only) | An optional **managed block** — added only if the file already exists | **Shared** — you own the file; VibeKB owns only its block |

The exact payload, the adapters, and the ownership rules are declared in
[`template/manifest.json`](./template/manifest.json), so "what belongs in another
repository" is explicit and versioned — not hidden in the installer's code.

### What is deliberately *not* installed

The marketing homepage (`index.php` and the root `assets/`) is VibeKB's own
landing page and would collide with your application's root, so it is not
installed — the product you use in a target repository is the **guide** at
`.vibekb/runtime/guide/`. Also excluded: VibeKB's own `.vibekb/` model,
`examples/`, and host-specific deploy config (`.cpanel.yml`, `DEPLOYMENT.md`).
VibeKB never writes generic root files such as `README`, `.gitignore`, or your
`docs/`.

## The installation flow

Running `vibekb install [target]` does the following, entirely in Go with no PHP:

1. **Resolve the target.** The argument (or the current directory) is the target.
   It refuses to install into VibeKB's own self-hosted repository (detected via
   `.vibekb/manifest.json` `self_hosted: true`).
2. **Refuse unrecognized collisions.** An existing `.vibekb/` that VibeKB did not
   create is a hard stop unless you pass `--force`.
3. **Detect the repository.** It confirms the target looks like a software project
   (a `.git` directory, common source folders, a README, or a manifest like
   `package.json`/`composer.json`/`go.mod`). If not, it asks for confirmation.
4. **Show the plan.** Before changing anything it prints exactly what will be
   **created** or **replaced** under `.vibekb/`, which adapters will run, and
   whether the model will be fresh or preserved. Managed-block inserts/updates/
   conflicts are previewed too.
5. **Copy the VibeKB-owned runtime.** From the binary's embedded payload, it
   writes (and on upgrade, refreshes) files under `.vibekb/runtime/`,
   `.vibekb/reference/`, and `.vibekb/prompts/`. Those paths are VibeKB's
   namespace — never your application code, and never root-level `CLAUDE.md` /
   `AGENTS.md` / `docs/`.
6. **Integrate optionally.** Namespaced adapters and managed blocks apply only
   under the selection rules below. Shared files are backed up under
   `.vibekb/backups/` before their first edit.
7. **Scaffold or preserve the model.** A fresh install writes an empty-but-valid
   model from the embedded starter definition. An upgrade preserves existing
   model records and only repairs missing scaffolding. Records
   `.vibekb/install.json` (ownership, hashes, provenance — no absolute paths).
8. **Verify (natively).** It checks the guide, tools, prompts, starter definition,
   starter model, and install manifest are present — no PHP is launched. (Run
   `vibekb check`, which does need PHP, to validate the model itself.)
9. **Hand off.** It prints the next action: build the model with
   `.vibekb/prompts/INTEGRATE_VIBEKB.md`.

### Options

| Option | Effect |
|--------|--------|
| `--dry-run` | Show every proposed change (including managed-block actions); write nothing. |
| `--yes`, `-y` | Assume "yes" to prompts — non-interactive install. |
| `--knowledge-only` | Install only `.vibekb/`; touch no integration files. |
| `--no-integrations` | Alias for `--knowledge-only`. |
| `--integrate LIST` | Install only the named adapters (`cursor`, `copilot`, `agents`, `claude`), creating them even if the tool/file is not detected. |
| `--force` | Narrowly scoped: permit taking over an *unrecognized* `.vibekb/` and reset the starter model files. Never overwrites shared files wholesale; never touches anything outside `.vibekb/` and the declared adapters. |
| `--upgrade` | Refresh the VibeKB-owned payload and preserve the model (auto-detected when a prior install exists). |
| `--help`, `-h` | Usage. |

### Dry run

```bash
vibekb install --dry-run /path/to/your/project
```

prints the create/replace plan and integration actions, and writes nothing. Use
it to see exactly what an install or upgrade would do.

## Upgrading

When the installer finds a prior installation (a `.vibekb/install.json` manifest,
or a legacy `.vibekb/.installer.json` marker), it switches to **upgrade mode**
automatically:

```bash
vibekb install /path/to/your/project     # auto-upgrade
# or force it explicitly:
vibekb install --upgrade /path/to/your/project
```

An upgrade **refreshes the VibeKB-owned payload** (everything under
`.vibekb/runtime/`, `.vibekb/reference/`, `.vibekb/prompts/`) and **preserves your
`.vibekb/` model** untouched. It also repairs any missing workspace scaffolding
without overwriting your content. To reset the starter model files as well, pass
`--force` (this is the only way an upgrade rewrites model records, and it still
never wholesale-overwrites `AGENTS.md` / `CLAUDE.md`).

The installer records the template version and per-file ownership/hashes in
`.vibekb/install.json` and shows the version transition (e.g. `1.0.0 → 2.0.0`) at
the top of the run. A pre-2.0 root-level install is detected and can be
consolidated with `vibekb migrate .` (see
[docs/REPOSITORY_SAFETY.md](./docs/REPOSITORY_SAFETY.md)).

## Migration — `vibekb migrate`

For repositories that still have a pre-2.0 root-level install
(`guide/`, `tools/`, root docs, `.vibekb/.installer.json`):

```bash
vibekb migrate --dry-run /path/to/your/project
vibekb migrate /path/to/your/project
```

Migration installs the consolidated `.vibekb/` layout, converts a recognised
whole-file VibeKB `CLAUDE.md`/`AGENTS.md` pointer into a managed block (title-line
+ signature gated — lookalike user files are preserved), relocates unmodified
reference docs, removes only unmodified root runtime directories, and backs up
shared files it rewrites. Ambiguous or modified files are left in place and
reported.

## Uninstall — `vibekb uninstall`

```bash
vibekb uninstall --dry-run /path/to/your/project
vibekb uninstall /path/to/your/project
vibekb uninstall --keep-knowledge /path/to/your/project
```

Removes VibeKB-owned files and strips only managed blocks from shared files.
`--keep-knowledge` retains model records and `.vibekb/backups/`. A full uninstall
relocates any shared-file backups to a stamped directory under the system temp
folder and prints that path so they are not deleted with `.vibekb/`.

## Repairing a workspace — `bootstrap`

`bootstrap` is "git init for VibeKB": it makes sure a valid, empty workspace
exists, creating any missing directories and starter files and **never
overwriting existing content**.

In a **target (consolidated) install**:

```bash
php .vibekb/runtime/tools/vibekb.php bootstrap            # verify and repair
php .vibekb/runtime/tools/vibekb.php bootstrap --dry-run  # report only
```

In **VibeKB's own self-hosted repository** the runtime still lives at the repo
root, so the equivalent is `php tools/vibekb.php bootstrap`.

Use it to recover a partially deleted or hand-damaged `.vibekb/`, or to create a
workspace in a repository that already has the VibeKB runtime. Like the
installer, it **never** generates functionality, invents diagrams, inspects your
source, or writes documentation about your software.

The native installer and `bootstrap` share one definition of the starter
workspace — the `template/starter/` data — so they can never disagree about what a
fresh model contains. (The Go installer embeds it; `bootstrap` reads the installed
copy via `tools/lib/Starter.php` under the runtime.)

## Template structure

The "template" is intentionally **not** a duplicated copy of the runtime (that
would drift instantly in a self-hosted repository). It is a small, declarative
payload manifest plus a language-neutral starter definition:

```
template/
    manifest.json     # declares the installable payload map, adapters,
                      # block markers, legacy paths, and deliberately-excluded paths
    integrations/     # namespaced adapters + managed-block body + WORKFLOW.md
    README.md         # orientation for the template system
    starter/          # the single source of truth for the fresh .vibekb/ workspace
        starter.json  #   the required directory list (incl. empty dirs)
        files/        #   the starter file tree, mirrored onto the target .vibekb/,
                      #   with {{DATE}} and {{PROJECT_NAME_JSON}} tokens
```

Both the Go installer (which embeds `template/starter/` and
`template/integrations/`) and PHP `bootstrap` (via `tools/lib/Starter.php`, which
reads the installed starter definition) consume this one definition.

A freshly scaffolded `.vibekb/` contains:

```
.vibekb/
    manifest.json                 # provenance intentionally blank until built
    install.json                  # ownership, hashes, provenance (written by install)
    project/                      # identity, intent, current-state, constraints (placeholders)
    functionality/index.json      # empty groups + order (no invented functionality)
    functionality/records/        # empty — the agent adds records
    system/                       # mental-model, components, flows, storage, deployment (placeholders)
    files/important-files.json    # empty
    diagrams/                     # index.json (empty) + records/ + assets/ + topology/
    memory/                       # decisions, constraints, assumptions, warnings, discoveries, changes
    work/current.md, work/handoff.md   # point at the integration prompt
    runtime/                      # guide, tools, template/starter (VibeKB-owned)
    reference/                    # PRODUCT, SCHEMA, INITIALIZE, MAINTENANCE, INSTALLER, WORKFLOW
    prompts/                      # INTEGRATE_VIBEKB.md
    .htaccess                     # deny direct web access to the model files
```

Every starter record is an explicit **placeholder** that tells the agent what to
write; none claims your software does anything. The provenance in
`manifest.json` is left blank on purpose — no commit was analysed, so none is
recorded. The empty model is valid and passes
`php .vibekb/runtime/tools/vibekb.php check` (or `php tools/vibekb.php check` in
this self-hosted repository).

## Future extension points

- **Add to the payload.** To install another VibeKB-owned file or directory, add
  a `src`→`dest` entry to `template/manifest.json` `payload.map`, **and** ensure
  the source path is covered by the embed directives in `embed.go` so the binary
  carries its bytes. Dry-run to confirm.
- **Change the starter workspace.** Edit the data under `template/starter/` —
  `starter.json` for directories, `files/` for starter files (keeping `{{DATE}}`
  and `{{PROJECT_NAME_JSON}}` tokens). Both the Go installer and PHP `bootstrap`
  update together. Keep every starter file valid so a fresh workspace still passes
  `check`.
- **Version the template.** Bump `template_version` in `template/manifest.json`
  when the payload or starter changes. The installer records it in
  `.vibekb/install.json` and reports the transition on upgrade.
- **Exclude new VibeKB-repo-only paths.** Distribution/tooling paths that must
  never install into a target belong in the `not_installed` list (documentation)
  and in the drift-exclusion set in `tools/vibekb.php`.

## Manual installation (advanced, fallback)

The `vibekb` binary is the supported path. If you must install by hand — for
example where you cannot run the binary — copy the payload declared in the `map`
of `template/manifest.json` into your repository, honoring each entry's `dest`
(everything lands under `.vibekb/`, e.g. `guide` → `.vibekb/runtime/guide`), then
create the workspace with:

```bash
php .vibekb/runtime/tools/vibekb.php bootstrap
```

That produces the same fresh, valid `.vibekb/` the installer would (it reads the
copied `.vibekb/runtime/template/starter/` definition). Then follow
[`INITIALIZE.md`](./INITIALIZE.md) to build the model.

## Troubleshooting

- **"The target is a VibeKB source/self-hosted repository."** You ran the installer
  against VibeKB's own repo. Install into a *different* repository, or use
  `php tools/vibekb.php bootstrap` to verify/repair this repo's workspace.
- **"A .vibekb/ directory already exists here but was not created by VibeKB."** An
  unrecognized `.vibekb/` is treated as a collision. Move/rename it, or re-run
  with `--force` only if you are certain taking it over is safe. `--force` still
  never wholesale-overwrites shared files.
- **`check` reports provenance warnings after install.** Expected — the model is
  empty until an agent builds it and records the analysed commit. The install is
  still valid.
