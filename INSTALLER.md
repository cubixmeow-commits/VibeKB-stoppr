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
it to **build the first VibeKB model using `prompts/INTEGRATE_VIBEKB.md`**.

Requirements: network access for the website installer (or a release binary / local
build). **PHP 8.2+ is required only to *run* the installed guide**
(`php tools/vibekb.php …`, the dynamic app), never to install. The website
installer supports macOS and Linux; Windows binaries are available from Releases.

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

The installer copies the **VibeKB-owned runtime** into your repository and creates
a fresh, empty **`.vibekb/` workspace**. Three responsibilities stay cleanly
separated:

| Where | What | Who owns it |
|-------|------|-------------|
| `guide/`, `tools/`, `prompts/`, `.cursor/`, `template/starter/`, VibeKB docs | The **installed runtime** — the app, the CLI, the agent instructions, and the starter definition `bootstrap` repairs from | VibeKB (safe to refresh on upgrade) |
| `.vibekb/` | The **living software model** — your project's knowledge | Your project (never overwritten) |
| `docs/` | The **generated static snapshot** (`php tools/vibekb.php generate`) | Generated output (not installed) |

The exact payload is declared in [`template/manifest.json`](./template/manifest.json),
so "what belongs in another repository" is explicit and versioned — not hidden in
the installer's code.

### What is deliberately *not* installed

The marketing homepage (`index.php` and the root `assets/`) is VibeKB's own
landing page and would collide with your application's root, so it is not
installed — the product you use in a target repository is the **guide** at
`guide/`. Also excluded: VibeKB's own `.vibekb/` model, `examples/`, generated
`docs/`, and host-specific deploy config (`.cpanel.yml`, `DEPLOYMENT.md`).

## The installation flow

Running `vibekb install [target]` does the following, entirely in Go with no PHP:

1. **Resolve the target.** The argument (or the current directory) is the target.
   It refuses to install into VibeKB's own self-hosted repository (detected via
   `.vibekb/manifest.json` `self_hosted: true`).
2. **Detect the repository.** It confirms the target looks like a software project
   (a `.git` directory, common source folders, a README, or a manifest like
   `package.json`/`composer.json`/`go.mod`). If not, it asks for confirmation.
3. **Show the plan.** Before changing anything it prints exactly what will be
   **created**, **replaced**, **skipped**, and whether `.vibekb/` will be a fresh
   model or preserved.
4. **Copy the runtime.** From the binary's embedded payload, it creates only
   missing directories and, on a fresh install, never overwrites a pre-existing
   file (those are skipped and reported) unless you pass `--force`. Your
   application's code is never touched.
5. **Scaffold a fresh model.** It writes an empty-but-valid `.vibekb/` from the
   embedded starter definition (see *Template structure* below) and records
   installer state in `.vibekb/.installer.json`.
6. **Verify (natively).** It checks the guide, tools, prompts, starter definition,
   and starter model are present, and confirms the scaffolded workspace is
   complete against the embedded definition — no PHP is launched. (Run
   `vibekb check`, which does need PHP, to validate the model itself.)
7. **Hand off.** It prints the next action: build the model with
   `prompts/INTEGRATE_VIBEKB.md`.

### Options

| Option | Effect |
|--------|--------|
| `--dry-run` | Show the full plan (every file) and change nothing. |
| `--yes`, `-y` | Assume "yes" to prompts — non-interactive install. |
| `--force` | Overwrite pre-existing files, **including** resetting an existing `.vibekb/` model. Never silent. |
| `--upgrade` | Refresh the runtime and preserve `.vibekb/` (auto-detected when a prior install exists). |
| `--help`, `-h` | Usage. |

### Dry run

```bash
vibekb install --dry-run /path/to/your/project
```

prints the create/replace/skip plan and the full per-file list, and writes
nothing. Use it to see exactly what an install or upgrade would do.

## Upgrading

When the installer finds a prior installation (a `.vibekb/.installer.json`
marker), it switches to **upgrade mode** automatically:

```bash
vibekb install /path/to/your/project     # auto-upgrade
# or force it explicitly:
vibekb install --upgrade /path/to/your/project
```

An upgrade **refreshes the VibeKB-owned payload** (`guide/`, `tools/`, `prompts/`,
`.cursor/`, docs) and **preserves your `.vibekb/` model** untouched. It also
repairs any missing workspace scaffolding without overwriting your content. To
reset the model as well, pass `--force` (this is the only way an upgrade touches
`.vibekb/`).

The installer records the template version in `.vibekb/.installer.json` and shows
the version transition (e.g. `1.0.0 → 1.1.0`) at the top of the run.

## Repairing a workspace — `bootstrap`

`bootstrap` is "git init for VibeKB": it makes sure a valid, empty workspace
exists, creating any missing directories and starter files and **never
overwriting existing content**.

```bash
php tools/vibekb.php bootstrap            # verify and repair
php tools/vibekb.php bootstrap --dry-run  # report only
```

Use it to recover a partially deleted or hand-damaged `.vibekb/`, or to create a
workspace in a repository that already has the VibeKB runtime. Like the
installer, it **never** generates functionality, invents diagrams, inspects your
source, or writes documentation about your software.

The native installer and `bootstrap` share one definition of the starter
workspace — the `template/starter/` data — so they can never disagree about what a
fresh model contains. (The Go installer embeds it; `bootstrap` reads the installed
copy via `tools/lib/Starter.php`.)

## Template structure

The "template" is intentionally **not** a duplicated copy of the runtime (that
would drift instantly in a self-hosted repository). It is a small, declarative
payload manifest plus a language-neutral starter definition:

```
template/
    manifest.json     # declares the installable payload, preserved paths,
                      # generated paths, and deliberately-excluded paths
    README.md         # orientation for the template system
    starter/          # the single source of truth for the fresh .vibekb/ workspace
        starter.json  #   the required directory list (incl. empty dirs)
        files/        #   the starter file tree, mirrored onto the target .vibekb/,
                      #   with {{DATE}} and {{PROJECT_NAME_JSON}} tokens
```

Both the Go installer (which embeds `template/starter/`) and PHP `bootstrap`
(via `tools/lib/Starter.php`, which reads it) consume this one definition.

A freshly scaffolded `.vibekb/` contains:

```
.vibekb/
    manifest.json                 # provenance intentionally blank until built
    project/                      # identity, intent, current-state, constraints (placeholders)
    functionality/index.json      # empty groups + order (no invented functionality)
    functionality/records/        # empty — the agent adds records
    system/                       # mental-model, components, flows, storage, deployment (placeholders)
    files/important-files.json    # empty
    diagrams/                     # index.json (empty) + records/ + assets/ + topology/
    memory/                       # decisions, constraints, assumptions, warnings, discoveries, changes
    work/current.md, work/handoff.md   # point at the integration prompt
    .htaccess                     # deny direct web access to the model files
    .installer.json               # installer state (version + owned payload)
```

Every starter record is an explicit **placeholder** that tells the agent what to
write; none claims your software does anything. The provenance in
`manifest.json` is left blank on purpose — no commit was analysed, so none is
recorded. The empty model is valid and passes `php tools/vibekb.php check`.

## Future extension points

- **Add to the payload.** To install another VibeKB-owned file or directory, add
  its repository-relative path to the appropriate list in
  `template/manifest.json` (`runtime`, `agent`, or `docs`), **and** add it to the
  embed directives in `embed.go` so the binary carries its bytes. Dry-run to
  confirm.
- **Change the starter workspace.** Edit the data under `template/starter/` —
  `starter.json` for directories, `files/` for starter files (keeping `{{DATE}}`
  and `{{PROJECT_NAME_JSON}}` tokens). Both the Go installer and PHP `bootstrap`
  update together. Keep every starter file valid so a fresh workspace still passes
  `check`.
- **Version the template.** Bump `template_version` in `template/manifest.json`
  when the payload or starter changes. The installer records it in
  `.vibekb/.installer.json` and reports the transition on upgrade.
- **Exclude new VibeKB-repo-only paths.** Distribution/tooling paths that must
  never install into a target belong in the `not_installed` list (documentation)
  and in the drift-exclusion set in `tools/vibekb.php`.

## Manual installation (advanced, appendix)

The `vibekb` binary is the supported path. If you must install by hand — for
example where you cannot run the binary — copy the payload declared in
`template/manifest.json` into your repository (`guide/`, `tools/`, `prompts/`,
`.cursor/`, `template/starter/`, and the VibeKB docs), then create the workspace
with:

```bash
php tools/vibekb.php bootstrap
```

That produces the same fresh, valid `.vibekb/` the installer would (it reads the
copied `template/starter/` definition). Then follow
[`INITIALIZE.md`](./INITIALIZE.md) to build the model.

## Troubleshooting

- **"The target is a VibeKB source/self-hosted repository."** You ran the installer
  against VibeKB's own repo. Install into a *different* repository, or use
  `php tools/vibekb.php bootstrap` to verify/repair this repo's workspace.
- **"existing file(s) were SKIPPED for safety."** A fresh install found files that
  already exist at payload paths. Review them; re-run with `--force` to replace,
  or remove/rename them first. Application code is never replaced without
  `--force`.
- **`check` reports provenance warnings after install.** Expected — the model is
  empty until an agent builds it and records the analysed commit. The install is
  still valid.
