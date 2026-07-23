<?php

declare(strict_types=1);

/**
 * Workspace location — layout-aware resolution of the active `.vibekb/`.
 *
 * VibeKB's runtime (`guide/` + `tools/`) can sit in two places relative to the
 * content root, and the PHP core must work in both without a build step:
 *
 *   - Self-hosted (VibeKB's own repo, and any legacy install):
 *       <repo>/guide, <repo>/tools, model at <repo>/.vibekb
 *       → runtimeRoot == <repo>; content root == <repo>/.vibekb
 *
 *   - Consolidated install (the safe default for target repositories):
 *       <repo>/.vibekb/runtime/guide, <repo>/.vibekb/runtime/tools,
 *       model at <repo>/.vibekb
 *       → runtimeRoot == <repo>/.vibekb/runtime; content root == <repo>/.vibekb
 *
 * Callers pass the runtime root (the directory that holds `guide/` and
 * `tools/`, i.e. `dirname(__DIR__)` from a tool or `__DIR__`'s parent from the
 * guide). The locator returns the content root (`.vibekb`); the project/git root
 * is always `dirname(content root)` in both layouts, so downstream code that
 * builds `<project>/.vibekb`, `git -C <project>`, or `<project>/docs` keeps
 * working unchanged.
 */

/**
 * Return the active content root (an absolute path to a `.vibekb` directory) for
 * a given runtime root, or null if none can be located.
 */
function vibekb_locate_content_root(string $runtimeRoot): ?string
{
    // Consolidated layout: the runtime lives *inside* the .vibekb tree. Walk up
    // to the nearest ancestor named `.vibekb` that carries a model manifest.
    $dir = $runtimeRoot;
    for ($i = 0; $i < 12; $i++) {
        if (basename($dir) === '.vibekb' && is_file($dir . '/manifest.json')) {
            return $dir;
        }
        $parent = dirname($dir);
        if ($parent === $dir) {
            break;
        }
        $dir = $parent;
    }

    // Self-hosted / sibling layout: the model is a `.vibekb` beside the runtime.
    $candidate = $runtimeRoot . '/.vibekb';
    if (is_dir($candidate)) {
        return $candidate;
    }

    return null;
}

/**
 * Return the project (git) root for a given runtime root: the parent of the
 * located content root, falling back to the runtime root itself.
 */
function vibekb_locate_project_root(string $runtimeRoot): string
{
    $content = vibekb_locate_content_root($runtimeRoot);
    return $content !== null ? dirname($content) : $runtimeRoot;
}
