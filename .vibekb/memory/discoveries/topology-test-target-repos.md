---
id: discovery-topology-test-target-repos
type: discovery
title: Topology test must discover local diagram fixtures
date: 2026-07-23
affects: []
verification: verified-from-source
---

## Discovery

Upstream `tools/test-topology.php` hard-codes VibeKB self-hosted diagram ids
(`self-maintenance-loop`, `content-load-flow`). Installed target repositories
like Stoppr fail that test even when their own topologies are valid.

## Action taken

Adapted the installed copy to discover a carrier diagram and a known-good
topology from the local `.vibekb/diagrams/` set (skipping the carrier when
asserting the good fixture). Next upstream upgrade may overwrite this file —
re-apply or contribute the project-agnostic fix to VibeKB.
