# VibeKB maintenance notes (Stoppr)

Stoppr hosts a VibeKB **0.2.0** consolidated install. Prefer the CLI:

```bash
vibekb status
vibekb check
vibekb generate
vibekb install --upgrade .
```

PHP equivalents:

```bash
php .vibekb/runtime/tools/vibekb.php status
php .vibekb/runtime/tools/vibekb.php check
php .vibekb/runtime/tools/vibekb.php generate
php .vibekb/runtime/tools/test-topology.php
```

Canonical product rules live under `.vibekb/reference/` (`PRODUCT.md`,
`SCHEMA.md`, `MAINTENANCE.md`, `INSTALLER.md`, `WORKFLOW.md`). Do not recreate
those files at the repository root.

GitHub Pages Mode B continues to publish from `/docs` (not
`.vibekb/generated/`), because Pages cannot serve a nested `.vibekb/` path.
