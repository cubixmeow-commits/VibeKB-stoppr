<?php

declare(strict_types=1);

require_once __DIR__ . '/FrontMatter.php';
require_once __DIR__ . '/Markdown.php';
require_once __DIR__ . '/Provenance.php';

/**
 * The VibeKB content layer.
 *
 * Loads the living software model from a repository-owned `.vibekb/`
 * directory: project records, functionality records, system explanations,
 * important files, repository memory, and current AI work. It resolves
 * relationships between records and validates the content set so the guide
 * can surface malformed or contradictory data instead of silently rendering
 * it.
 *
 * All filesystem access is confined to the configured content root. Record
 * ids are constrained to a safe character set, so a crafted `?id=` value can
 * never escape the content directory.
 */
final class Content
{
    private string $root;
    private bool $loaded = false;

    /** @var array<string, mixed> */
    private array $manifest = [];
    /** @var array<string, array{meta: array<string,mixed>, html: string, body: string}> */
    private array $project = [];
    /** @var array<string, array<string, mixed>> id => record */
    private array $functionality = [];
    /** @var array<string, mixed> functionality index config */
    private array $functionalityIndex = [];
    /** @var array<string, array{meta: array<string,mixed>, html: string, body: string}> */
    private array $system = [];
    /** @var list<array<string, mixed>> */
    private array $files = [];
    /** @var array<string, array<string, mixed>> id => diagram record */
    private array $diagrams = [];
    /** @var array<string, mixed> diagrams index config */
    private array $diagramsIndex = [];
    /** @var array<string, array{version:int, nodes: array<string,array<string,mixed>>, edges: list<array<string,mixed>>, file: string}> diagram id => normalised topology */
    private array $topology = [];
    /** @var array<string, string|bool> normalised source provenance (for source links) */
    private array $sourceProvenance = [];
    /** @var array<string, array<string,mixed>>|null important-files index by path */
    private ?array $fileIndex = null;
    /** @var array<string, array<string, array<string, mixed>>> type => id => record */
    private array $memory = [];
    /** @var array<string, mixed>|null */
    private ?array $currentWork = null;
    /** @var array<string, mixed>|null */
    private ?array $handoff = null;
    /** @var list<array<string, mixed>> */
    private array $sessions = [];
    /** @var list<array{level: string, message: string}> */
    private array $issues = [];

    private const MEMORY_TYPES = ['decisions', 'constraints', 'assumptions', 'warnings', 'discoveries', 'changes'];
    private const SYSTEM_DOCS = ['mental-model', 'components', 'request-flow', 'data-flow', 'storage', 'deployment'];
    /** Explainable-diagram topology schema versions this loader understands. */
    private const TOPOLOGY_VERSIONS = [1];

    public function __construct(string $root)
    {
        $this->root = rtrim($root, '/');
    }

    public function load(): void
    {
        if ($this->loaded) {
            return;
        }
        $this->loaded = true;

        $this->manifest = $this->readJson('manifest.json');
        $this->sourceProvenance = provenance_data($this->manifest);

        foreach (['identity', 'intent', 'current-state', 'constraints'] as $name) {
            $doc = $this->readMarkdown('project/' . $name . '.md');
            if ($doc !== null) {
                $this->project[$name] = $doc;
            }
        }

        $this->functionalityIndex = $this->readJson('functionality/index.json');
        $dir = $this->root . '/functionality/records';
        foreach ($this->safeGlob($dir, '*.md') as $file) {
            $doc = $this->loadRecord($file, 'functionality');
            if ($doc === null) {
                continue;
            }
            $id = (string) ($doc['meta']['id'] ?? basename($file, '.md'));
            if (isset($this->functionality[$id])) {
                $this->issues[] = ['level' => 'error', 'message' => "Duplicate functionality id: {$id}"];
            }
            $this->functionality[$id] = $doc;
        }

        foreach (self::SYSTEM_DOCS as $name) {
            $doc = $this->readMarkdown('system/' . $name . '.md');
            if ($doc !== null) {
                $this->system[$name] = $doc;
            }
        }

        $filesData = $this->readJson('files/important-files.json');
        $this->files = is_array($filesData['files'] ?? null) ? $filesData['files'] : [];

        $this->diagramsIndex = $this->readJson('diagrams/index.json');
        foreach ($this->safeGlob($this->root . '/diagrams/records', '*.md') as $file) {
            $doc = $this->loadRecord($file, 'diagram');
            if ($doc === null) {
                continue;
            }
            $id = (string) ($doc['meta']['id'] ?? basename($file, '.md'));
            if (isset($this->diagrams[$id])) {
                $this->issues[] = ['level' => 'error', 'message' => "Duplicate diagram id: {$id}"];
            }
            $this->diagrams[$id] = $doc;
        }

        // Explainable-diagram topology is optional and loaded per diagram that
        // references it. A malformed or missing topology is reported as an issue,
        // never fatal — the diagram still renders as a picture + narrative.
        foreach ($this->diagrams as $id => $rec) {
            $topoName = (string) ($rec['meta']['topology'] ?? '');
            if ($topoName !== '') {
                $this->loadTopology((string) $id, $topoName);
            }
        }

        foreach (self::MEMORY_TYPES as $type) {
            $this->memory[$type] = [];
            foreach ($this->safeGlob($this->root . '/memory/' . $type, '*.md') as $file) {
                $doc = $this->loadRecord($file, rtrim($type, 's'));
                if ($doc === null) {
                    continue;
                }
                $id = (string) ($doc['meta']['id'] ?? basename($file, '.md'));
                if (isset($this->memory[$type][$id])) {
                    $this->issues[] = ['level' => 'error', 'message' => "Duplicate {$type} id: {$id}"];
                }
                $this->memory[$type][$id] = $doc;
            }
        }

        $this->currentWork = $this->readMarkdown('work/current.md');
        $this->handoff = $this->readMarkdown('work/handoff.md');
        foreach ($this->safeGlob($this->root . '/work/sessions', '*.md') as $file) {
            $doc = $this->loadRecord($file, 'session');
            if ($doc !== null) {
                $this->sessions[] = $doc;
            }
        }
        usort($this->sessions, fn ($a, $b) => strcmp((string) ($b['meta']['date'] ?? ''), (string) ($a['meta']['date'] ?? '')));

        $this->validate();
    }

    // ---- accessors -----------------------------------------------------

    /** @return array<string, mixed> */
    public function manifest(): array
    {
        return $this->manifest;
    }

    /** @return array{meta: array<string,mixed>, html: string, body: string}|null */
    public function projectDoc(string $name): ?array
    {
        return $this->project[$name] ?? null;
    }

    /** @return array{meta: array<string,mixed>, html: string, body: string}|null */
    public function systemDoc(string $name): ?array
    {
        return $this->system[$name] ?? null;
    }

    /** @return array<string, array<string,mixed>> */
    public function allFunctionality(): array
    {
        return $this->functionality;
    }

    /** @return array<string, mixed>|null */
    public function functionality(string $id): ?array
    {
        return $this->functionality[$id] ?? null;
    }

    /**
     * Functionality grouped by area, using the ordering defined in
     * functionality/index.json. Records in unknown areas fall into an
     * "Other" group so nothing is silently dropped.
     *
     * @return list<array{id: string, title: string, description: string, records: list<array<string,mixed>>}>
     */
    public function functionalityGroups(): array
    {
        $groupDefs = is_array($this->functionalityIndex['groups'] ?? null)
            ? $this->functionalityIndex['groups']
            : [];
        $order = is_array($this->functionalityIndex['order'] ?? null)
            ? $this->functionalityIndex['order']
            : [];

        $rank = array_flip(array_map('strval', $order));
        $records = $this->functionality;
        uasort($records, function ($a, $b) use ($rank) {
            $ai = $rank[$a['meta']['id'] ?? ''] ?? 999;
            $bi = $rank[$b['meta']['id'] ?? ''] ?? 999;
            if ($ai === $bi) {
                return strcmp((string) ($a['meta']['title'] ?? ''), (string) ($b['meta']['title'] ?? ''));
            }
            return $ai <=> $bi;
        });

        $groups = [];
        $groupIndex = [];
        foreach ($groupDefs as $g) {
            if (!is_array($g)) {
                continue;
            }
            $gid = (string) ($g['id'] ?? '');
            if ($gid === '') {
                continue;
            }
            $groupIndex[$gid] = count($groups);
            $groups[] = [
                'id' => $gid,
                'title' => (string) ($g['title'] ?? ucfirst(str_replace('-', ' ', $gid))),
                'description' => (string) ($g['description'] ?? ''),
                'records' => [],
            ];
        }

        foreach ($records as $rec) {
            $area = (string) ($rec['meta']['area'] ?? 'other');
            if (!isset($groupIndex[$area])) {
                $groupIndex[$area] = count($groups);
                $groups[] = [
                    'id' => $area,
                    'title' => ucfirst(str_replace('-', ' ', $area)),
                    'description' => '',
                    'records' => [],
                ];
            }
            $groups[$groupIndex[$area]]['records'][] = $rec;
        }

        return array_values(array_filter($groups, fn ($g) => $g['records'] !== []));
    }

    /** @return list<array<string, mixed>> */
    public function files(): array
    {
        return $this->files;
    }

    /** @return array<string, array<string,mixed>> */
    public function allDiagrams(): array
    {
        return $this->diagrams;
    }

    /**
     * Normalise a front-matter value (scalar or list) to a list of strings.
     * Public wrapper around the internal helper for use in templates.
     *
     * @param mixed $value
     * @return list<string>
     */
    public function asList(mixed $value): array
    {
        return $this->toList($value);
    }

    /** @return array<string, mixed>|null */
    public function diagram(string $id): ?array
    {
        return $this->diagrams[$id] ?? null;
    }

    /**
     * Diagrams grouped by the groups defined in diagrams/index.json, ordered by
     * the index `order`. Diagrams in unknown groups fall into an "Other" group
     * so nothing is silently dropped.
     *
     * @return list<array{id: string, title: string, description: string, records: list<array<string,mixed>>}>
     */
    public function diagramGroups(): array
    {
        $groupDefs = is_array($this->diagramsIndex['groups'] ?? null) ? $this->diagramsIndex['groups'] : [];
        $order = is_array($this->diagramsIndex['order'] ?? null) ? $this->diagramsIndex['order'] : [];
        $rank = array_flip(array_map('strval', $order));

        $records = $this->diagrams;
        uasort($records, function ($a, $b) use ($rank) {
            $ai = $rank[$a['meta']['id'] ?? ''] ?? 999;
            $bi = $rank[$b['meta']['id'] ?? ''] ?? 999;
            if ($ai === $bi) {
                return strcmp((string) ($a['meta']['title'] ?? ''), (string) ($b['meta']['title'] ?? ''));
            }
            return $ai <=> $bi;
        });

        $groups = [];
        $groupIndex = [];
        foreach ($groupDefs as $g) {
            if (!is_array($g)) {
                continue;
            }
            $gid = (string) ($g['id'] ?? '');
            if ($gid === '') {
                continue;
            }
            $groupIndex[$gid] = count($groups);
            $groups[] = [
                'id' => $gid,
                'title' => (string) ($g['title'] ?? ucfirst(str_replace('-', ' ', $gid))),
                'description' => (string) ($g['description'] ?? ''),
                'records' => [],
            ];
        }
        foreach ($records as $rec) {
            $group = (string) ($rec['meta']['group'] ?? 'other');
            if (!isset($groupIndex[$group])) {
                $groupIndex[$group] = count($groups);
                $groups[] = [
                    'id' => $group,
                    'title' => ucfirst(str_replace('-', ' ', $group)),
                    'description' => '',
                    'records' => [],
                ];
            }
            $groups[$groupIndex[$group]]['records'][] = $rec;
        }

        return array_values(array_filter($groups, fn ($g) => $g['records'] !== []));
    }

    /**
     * Inline SVG markup for a diagram, read from diagrams/assets/ (confined to
     * the content root). The XML declaration is stripped so the markup can be
     * embedded directly in an HTML page. Returns null if the asset is missing.
     */
    public function diagramSvg(string $svgFile): ?string
    {
        $svgFile = (string) preg_replace('#[^a-z0-9\-_.]#i', '', basename($svgFile));
        if ($svgFile === '') {
            return null;
        }
        $path = $this->root . '/diagrams/assets/' . $svgFile;
        if (!$this->isInsideRoot($path) || !is_file($path)) {
            return null;
        }
        $raw = @file_get_contents($path);
        if ($raw === false) {
            return null;
        }
        // Drop the XML prolog; inline SVG in HTML must not carry it.
        return trim((string) preg_replace('/^\s*<\?xml.*?\?>\s*/s', '', $raw));
    }

    /**
     * Resolve a list of diagram ids into linkable summaries.
     *
     * @param mixed $ids
     * @return list<array{id: string, title: string, resolved: bool}>
     */
    public function resolveDiagrams(mixed $ids): array
    {
        $out = [];
        foreach ($this->toList($ids) as $id) {
            $rec = $this->diagrams[$id] ?? null;
            $out[] = [
                'id' => $id,
                'title' => $rec !== null ? (string) ($rec['meta']['title'] ?? $id) : $id,
                'resolved' => $rec !== null,
            ];
        }
        return $out;
    }

    /**
     * Diagrams that declare a relationship to a functionality id.
     *
     * @return list<array{id: string, title: string, resolved: bool}>
     */
    public function diagramsForFunctionality(string $id): array
    {
        $out = [];
        foreach ($this->diagrams as $did => $rec) {
            $fns = $this->toList($rec['meta']['functionality'] ?? []);
            if (in_array($id, $fns, true)) {
                $out[] = ['id' => $did, 'title' => (string) ($rec['meta']['title'] ?? $did), 'resolved' => true];
            }
        }
        return $out;
    }

    // ---- explainable-diagram topology ---------------------------------

    /** Whether a diagram has a loaded (well-formed) explainable topology. */
    public function hasTopology(string $diagramId): bool
    {
        return isset($this->topology[$diagramId]);
    }

    /**
     * Raw normalised topology for a diagram (nodes keyed by id, edges as a
     * list), or null if the diagram has no topology or it failed to load.
     *
     * @return array{version:int, nodes: array<string,array<string,mixed>>, edges: list<array<string,mixed>>, file: string}|null
     */
    public function diagramTopology(string $diagramId): ?array
    {
        return $this->topology[$diagramId] ?? null;
    }

    /** Normalised source provenance used to build immutable source links. */
    public function sourceProvenance(): array
    {
        return $this->sourceProvenance;
    }

    /**
     * Topology nodes (across all diagrams) that reference a functionality id, so
     * a functionality page can deep-link to the exact node that represents it.
     *
     * @return list<array{diagram: string, diagram_title: string, node: string, node_title: string}>
     */
    public function diagramNodesForFunctionality(string $fnId): array
    {
        $out = [];
        foreach ($this->topology as $did => $topo) {
            $dTitle = (string) ($this->diagrams[$did]['meta']['title'] ?? $did);
            foreach ($topo['nodes'] as $nid => $n) {
                if (in_array($fnId, $this->toList($n['functionality'] ?? []), true)) {
                    $out[] = [
                        'diagram' => (string) $did,
                        'diagram_title' => $dTitle,
                        'node' => (string) $nid,
                        'node_title' => (string) ($n['title'] ?? $nid),
                    ];
                }
            }
        }
        return $out;
    }

    /**
     * A fully resolved, template-ready projection of a diagram's topology:
     * nodes and edges with their functionality/warning links resolved, files
     * enriched with canonical metadata from important-files.json and an
     * immutable source link, and endpoint titles filled in. Returns null when
     * the diagram has no usable topology.
     *
     * @return array{version:int, nodes: array<string,array<string,mixed>>, edges: list<array<string,mixed>>}|null
     */
    public function resolvedTopology(string $diagramId): ?array
    {
        $topo = $this->topology[$diagramId] ?? null;
        if ($topo === null) {
            return null;
        }

        $prov = $this->sourceProvenance;
        $nodeTitles = [];
        foreach ($topo['nodes'] as $nid => $n) {
            $nodeTitles[$nid] = (string) ($n['title'] ?? $nid);
        }

        $resolveFiles = function (mixed $files) use ($prov): array {
            $out = [];
            if (!is_array($files)) {
                return $out;
            }
            foreach ($files as $f) {
                if (!is_array($f)) {
                    continue;
                }
                $path = (string) ($f['path'] ?? '');
                if ($path === '') {
                    continue;
                }
                $canon = $this->fileMeta($path);
                $out[] = [
                    'path' => $path,
                    'role' => (string) ($f['role'] ?? ''),
                    'reason' => (string) ($f['reason'] ?? ''),
                    'canonical_purpose' => (string) ($canon['purpose'] ?? ''),
                    'safety' => (string) ($canon['safety'] ?? ''),
                    'provenance' => (string) ($canon['provenance'] ?? ''),
                    'known' => $canon !== null,
                    'url' => source_link_url($prov, $path, (string) ($f['lines'] ?? '')),
                ];
            }
            return $out;
        };

        $nodes = [];
        foreach ($topo['nodes'] as $nid => $n) {
            $nodes[$nid] = [
                'id' => $nid,
                'title' => (string) ($n['title'] ?? $nid),
                'purpose' => (string) ($n['purpose'] ?? ''),
                'location' => (string) ($n['location'] ?? ''),
                'functionality' => $this->resolveFunctionality($n['functionality'] ?? []),
                'warnings' => $this->resolveMemory(array_map(
                    fn ($w) => 'warning:' . $w,
                    $this->toList($n['warnings'] ?? [])
                )),
                'files' => $resolveFiles($n['files'] ?? []),
                'verification' => (string) ($n['verification'] ?? ''),
                'uncertainty' => (string) ($n['uncertainty'] ?? ''),
            ];
        }

        $edges = [];
        foreach ($topo['edges'] as $e) {
            $from = (string) ($e['from'] ?? '');
            $to = (string) ($e['to'] ?? '');
            $ver = (string) ($e['verification'] ?? '');
            $edges[] = [
                'id' => (string) ($e['id'] ?? ''),
                'from' => $from,
                'to' => $to,
                'from_title' => $nodeTitles[$from] ?? $from,
                'to_title' => $nodeTitles[$to] ?? $to,
                'mechanism' => (string) ($e['mechanism'] ?? ''),
                'label' => (string) ($e['label'] ?? ''),
                'explanation' => (string) ($e['explanation'] ?? ''),
                'basis' => (string) ($e['basis'] ?? ''),
                'functionality' => $this->resolveFunctionality($e['functionality'] ?? []),
                'warnings' => $this->resolveMemory(array_map(
                    fn ($w) => 'warning:' . $w,
                    $this->toList($e['warnings'] ?? [])
                )),
                'files' => $resolveFiles($e['files'] ?? []),
                'verification' => $ver,
                'verified' => edge_verification_is_verified($ver),
                'uncertainty' => (string) ($e['uncertainty'] ?? ''),
            ];
        }

        return ['version' => $topo['version'], 'nodes' => $nodes, 'edges' => $edges];
    }

    /**
     * Canonical metadata for an important file by path, or null if the file is
     * not in important-files.json. Lets diagram topology reuse the curated
     * purpose/safety/provenance instead of restating them.
     *
     * @return array<string, mixed>|null
     */
    public function fileMeta(string $path): ?array
    {
        if ($this->fileIndex === null) {
            $this->fileIndex = [];
            foreach ($this->files as $file) {
                $p = (string) ($file['path'] ?? '');
                if ($p !== '') {
                    $this->fileIndex[$p] = $file;
                }
            }
        }
        return $this->fileIndex[$path] ?? null;
    }

    /**
     * Extract the `data-vibekb-node` / `data-vibekb-edge` marker ids present in
     * a diagram SVG, so validation can confirm the SVG and topology agree.
     *
     * @return array{nodes: list<string>, edges: list<string>}
     */
    public function diagramSvgMarkers(string $svgFile): array
    {
        $out = ['nodes' => [], 'edges' => []];
        $svgFile = (string) preg_replace('#[^a-z0-9\-_.]#i', '', basename($svgFile));
        if ($svgFile === '') {
            return $out;
        }
        $path = $this->root . '/diagrams/assets/' . $svgFile;
        if (!$this->isInsideRoot($path) || !is_file($path)) {
            return $out;
        }
        $raw = (string) @file_get_contents($path);
        if ($raw === '') {
            return $out;
        }
        $dom = new DOMDocument();
        if (@$dom->loadXML($raw) === false) {
            return $out;
        }
        $xp = new DOMXPath($dom);
        foreach ($xp->query('//*[@data-vibekb-node]') as $el) {
            $out['nodes'][] = $el->getAttribute('data-vibekb-node');
        }
        foreach ($xp->query('//*[@data-vibekb-edge]') as $el) {
            $out['edges'][] = $el->getAttribute('data-vibekb-edge');
        }
        return $out;
    }

    /** @return array<string, array<string, array<string,mixed>>> */
    public function memory(): array
    {
        return $this->memory;
    }

    /** @return array<string, array<string,mixed>> */
    public function memoryByType(string $type): array
    {
        return $this->memory[$type] ?? [];
    }

    /** @return array<string, mixed>|null */
    public function memoryRecord(string $type, string $id): ?array
    {
        return $this->memory[$type][$id] ?? null;
    }

    /** @return array<string, mixed>|null */
    public function currentWork(): ?array
    {
        return $this->currentWork;
    }

    /** @return array<string, mixed>|null */
    public function handoff(): ?array
    {
        return $this->handoff;
    }

    /** @return list<array<string, mixed>> */
    public function sessions(): array
    {
        return $this->sessions;
    }

    /** @return array<string, array<string,mixed>> change records */
    public function changes(): array
    {
        return $this->memory['changes'] ?? [];
    }

    /** @return list<array{level: string, message: string}> */
    public function issues(): array
    {
        return $this->issues;
    }

    /**
     * Count functionality by status for the overview dashboard.
     *
     * @return array<string, int>
     */
    public function statusCounts(): array
    {
        $counts = [];
        foreach ($this->functionality as $rec) {
            $status = (string) ($rec['meta']['status'] ?? 'unknown');
            $counts[$status] = ($counts[$status] ?? 0) + 1;
        }
        return $counts;
    }

    // ---- relationship resolution --------------------------------------

    /**
     * Resolve a list of functionality ids into linkable summaries. Unknown
     * ids are returned with resolved=false so templates can flag them.
     *
     * @param mixed $ids
     * @return list<array{id: string, title: string, resolved: bool}>
     */
    public function resolveFunctionality(mixed $ids): array
    {
        $out = [];
        foreach ($this->toList($ids) as $id) {
            $rec = $this->functionality[$id] ?? null;
            $out[] = [
                'id' => $id,
                'title' => $rec !== null ? (string) ($rec['meta']['title'] ?? $id) : $id,
                'resolved' => $rec !== null,
            ];
        }
        return $out;
    }

    /**
     * Resolve "type:id" memory references (e.g. "decision:sqlite-over-mysql").
     *
     * @param mixed $refs
     * @return list<array{type: string, id: string, title: string, resolved: bool}>
     */
    public function resolveMemory(mixed $refs): array
    {
        $out = [];
        foreach ($this->toList($refs) as $ref) {
            if (!str_contains($ref, ':')) {
                $out[] = ['type' => '', 'id' => $ref, 'title' => $ref, 'resolved' => false];
                continue;
            }
            [$singular, $id] = explode(':', $ref, 2);
            $type = $this->pluralMemoryType($singular);
            $rec = $this->memory[$type][$id] ?? null;
            $out[] = [
                'type' => $type,
                'id' => $id,
                'title' => $rec !== null ? (string) ($rec['meta']['title'] ?? $id) : $ref,
                'resolved' => $rec !== null,
            ];
        }
        return $out;
    }

    /**
     * Functionality records that declare a dependency on $id (reverse links).
     *
     * @return list<array{id: string, title: string, resolved: bool}>
     */
    public function dependentsOf(string $id): array
    {
        $out = [];
        foreach ($this->functionality as $rid => $rec) {
            $deps = $this->toList($rec['meta']['depends_on'] ?? []);
            if (in_array($id, $deps, true)) {
                $out[] = ['id' => $rid, 'title' => (string) ($rec['meta']['title'] ?? $rid), 'resolved' => true];
            }
        }
        return $out;
    }

    /**
     * Important-file records that participate in a functionality.
     *
     * @return list<array<string, mixed>>
     */
    public function filesForFunctionality(string $id): array
    {
        $out = [];
        foreach ($this->files as $file) {
            $fns = $this->toList($file['functionality'] ?? []);
            if (in_array($id, $fns, true)) {
                $out[] = $file;
            }
        }
        return $out;
    }

    /**
     * Memory records that link back to a functionality id, grouped by type.
     *
     * @return array<string, list<array<string, mixed>>>
     */
    public function memoryForFunctionality(string $id): array
    {
        $out = [];
        foreach ($this->memory as $type => $records) {
            foreach ($records as $rec) {
                $fns = $this->toList($rec['meta']['functionality'] ?? []);
                if (in_array($id, $fns, true)) {
                    $out[$type][] = $rec;
                }
            }
        }
        return $out;
    }

    // ---- validation ----------------------------------------------------

    private function validate(): void
    {
        $statusVocab = array_keys(status_vocabulary());
        $verVocab = array_keys(verification_vocabulary());
        $safetyVocab = array_keys(safety_vocabulary());

        foreach ($this->functionality as $id => $rec) {
            $meta = $rec['meta'];
            foreach (['id', 'title', 'status', 'summary'] as $req) {
                if (($meta[$req] ?? '') === '') {
                    $this->issues[] = ['level' => 'error', 'message' => "Functionality '{$id}' missing required field: {$req}"];
                }
            }
            $status = (string) ($meta['status'] ?? '');
            if ($status !== '' && !in_array($status, $statusVocab, true)) {
                $this->issues[] = ['level' => 'error', 'message' => "Functionality '{$id}' has unknown status: {$status}"];
            }
            $ver = (string) ($meta['verification'] ?? '');
            if ($ver !== '' && !in_array($ver, $verVocab, true)) {
                $this->issues[] = ['level' => 'error', 'message' => "Functionality '{$id}' has unknown verification: {$ver}"];
            }
            foreach ($this->toList($meta['depends_on'] ?? []) as $dep) {
                if (!isset($this->functionality[$dep])) {
                    $this->issues[] = ['level' => 'error', 'message' => "Functionality '{$id}' depends on unknown functionality: {$dep}"];
                }
            }
            foreach ($this->resolveMemory($meta['related_memory'] ?? []) as $ref) {
                if (!$ref['resolved']) {
                    $this->issues[] = ['level' => 'warn', 'message' => "Functionality '{$id}' references unresolved memory: {$ref['id']}"];
                }
            }
        }

        foreach ($this->memory as $type => $records) {
            foreach ($records as $id => $rec) {
                foreach ($this->toList($rec['meta']['functionality'] ?? []) as $fn) {
                    if (!isset($this->functionality[$fn])) {
                        $this->issues[] = ['level' => 'warn', 'message' => ucfirst($type) . " '{$id}' links to unknown functionality: {$fn}"];
                    }
                }
            }
        }

        foreach ($this->files as $file) {
            $path = (string) ($file['path'] ?? '(unnamed)');
            $safety = (string) ($file['safety'] ?? '');
            if ($safety !== '' && !in_array($safety, $safetyVocab, true)) {
                $this->issues[] = ['level' => 'error', 'message' => "File '{$path}' has unknown safety level: {$safety}"];
            }
            foreach ($this->toList($file['functionality'] ?? []) as $fn) {
                if (!isset($this->functionality[$fn])) {
                    $this->issues[] = ['level' => 'warn', 'message' => "File '{$path}' links to unknown functionality: {$fn}"];
                }
            }
        }

        foreach ($this->diagrams as $id => $rec) {
            $meta = $rec['meta'];
            foreach (['id', 'title', 'svg'] as $req) {
                if (($meta[$req] ?? '') === '') {
                    $this->issues[] = ['level' => 'error', 'message' => "Diagram '{$id}' missing required field: {$req}"];
                }
            }
            $ver = (string) ($meta['verification'] ?? '');
            if ($ver !== '' && !in_array($ver, $verVocab, true)) {
                $this->issues[] = ['level' => 'error', 'message' => "Diagram '{$id}' has unknown verification: {$ver}"];
            }

            $svgFile = (string) ($meta['svg'] ?? '');
            if ($svgFile !== '') {
                $svgPath = $this->root . '/diagrams/assets/' . basename($svgFile);
                if (!$this->isInsideRoot($svgPath) || !is_file($svgPath)) {
                    $this->issues[] = ['level' => 'error', 'message' => "Diagram '{$id}' references missing SVG asset: {$svgFile}"];
                } else {
                    $dom = new DOMDocument();
                    $raw = (string) @file_get_contents($svgPath);
                    if (@$dom->loadXML($raw) === false) {
                        $this->issues[] = ['level' => 'error', 'message' => "Diagram '{$id}' SVG is not well-formed XML: {$svgFile}"];
                    } else {
                        $xp = new DOMXPath($dom);
                        if ($xp->query('//*[local-name()="title"]')->length === 0) {
                            $this->issues[] = ['level' => 'error', 'message' => "Diagram '{$id}' SVG is missing an accessible <title>: {$svgFile}"];
                        }
                        if ($xp->query('//*[local-name()="desc"]')->length === 0) {
                            $this->issues[] = ['level' => 'warn', 'message' => "Diagram '{$id}' SVG is missing an accessible <desc>: {$svgFile}"];
                        }
                    }
                }
            }

            foreach ($this->toList($meta['functionality'] ?? []) as $fn) {
                if (!isset($this->functionality[$fn])) {
                    $this->issues[] = ['level' => 'warn', 'message' => "Diagram '{$id}' links to unknown functionality: {$fn}"];
                }
            }
            foreach ($this->toList($meta['warnings'] ?? []) as $wn) {
                if (!isset($this->memory['warnings'][$wn])) {
                    $this->issues[] = ['level' => 'warn', 'message' => "Diagram '{$id}' links to unknown warning: {$wn}"];
                }
            }
            foreach ($this->toList($meta['diagrams'] ?? []) as $dg) {
                if (!isset($this->diagrams[$dg])) {
                    $this->issues[] = ['level' => 'warn', 'message' => "Diagram '{$id}' links to unknown diagram: {$dg}"];
                }
            }

            // Explainable-diagram topology: a compatibility note when absent, the
            // full contract when present.
            if (($meta['topology'] ?? '') === '') {
                $this->issues[] = ['level' => 'warn', 'message' => "Diagram '{$id}' has no explainable topology yet (renders as a picture + narrative only)."];
            } elseif (isset($this->topology[$id])) {
                $this->validateTopology($id, (string) ($meta['svg'] ?? ''));
            }
        }
    }

    /**
     * Semantic validation of a loaded explainable-diagram topology and its
     * agreement with the SVG markers. Structural problems were already reported
     * by loadTopology(); this enforces the explainability contract: every node
     * has a purpose, every edge names a controlled mechanism and states an
     * explanation, endpoints resolve, verification is honest, references resolve,
     * every displayed file has a reason, and the SVG and topology map both ways.
     */
    private function validateTopology(string $diagramId, string $svgFile): void
    {
        $topo = $this->topology[$diagramId];
        $verVocab = array_keys(verification_vocabulary());
        $mechVocab = array_keys(edge_mechanism_vocabulary());
        $roleVocab = array_keys(file_role_vocabulary());

        $checkFiles = function (mixed $files, string $where) use ($diagramId, $roleVocab): void {
            if (!is_array($files)) {
                return;
            }
            foreach ($files as $f) {
                if (!is_array($f)) {
                    continue;
                }
                $path = (string) ($f['path'] ?? '');
                if ($path === '') {
                    $this->issues[] = ['level' => 'error', 'message' => "Diagram '{$diagramId}' {$where} has a file entry with no path."];
                    continue;
                }
                $norm = str_replace('\\', '/', $path);
                if (str_starts_with($norm, '/') || str_contains($norm, '../') || str_contains($norm, "\0")) {
                    $this->issues[] = ['level' => 'error', 'message' => "Diagram '{$diagramId}' {$where} has an unsafe (non-repository-relative) file path: {$path}"];
                }
                if (trim((string) ($f['reason'] ?? '')) === '') {
                    $this->issues[] = ['level' => 'error', 'message' => "Diagram '{$diagramId}' {$where} shows file '{$path}' without a reason."];
                }
                $role = (string) ($f['role'] ?? '');
                if ($role !== '' && !in_array($role, $roleVocab, true)) {
                    $this->issues[] = ['level' => 'warn', 'message' => "Diagram '{$diagramId}' {$where} file '{$path}' has an unlisted role: {$role}"];
                }
            }
        };

        // Nodes.
        foreach ($topo['nodes'] as $nid => $n) {
            if (trim((string) ($n['title'] ?? '')) === '') {
                $this->issues[] = ['level' => 'error', 'message' => "Diagram '{$diagramId}' node '{$nid}' is missing a title."];
            }
            if (trim((string) ($n['purpose'] ?? '')) === '') {
                $this->issues[] = ['level' => 'error', 'message' => "Diagram '{$diagramId}' node '{$nid}' is missing a purpose (every node must answer 'what is this?')."];
            }
            $ver = (string) ($n['verification'] ?? '');
            if ($ver !== '' && !in_array($ver, $verVocab, true)) {
                $this->issues[] = ['level' => 'error', 'message' => "Diagram '{$diagramId}' node '{$nid}' has unknown verification: {$ver}"];
            }
            foreach ($this->toList($n['functionality'] ?? []) as $fn) {
                if (!isset($this->functionality[$fn])) {
                    $this->issues[] = ['level' => 'warn', 'message' => "Diagram '{$diagramId}' node '{$nid}' references unknown functionality: {$fn}"];
                }
            }
            foreach ($this->toList($n['warnings'] ?? []) as $wn) {
                if (!isset($this->memory['warnings'][$wn])) {
                    $this->issues[] = ['level' => 'warn', 'message' => "Diagram '{$diagramId}' node '{$nid}' references unknown warning: {$wn}"];
                }
            }
            $checkFiles($n['files'] ?? [], "node '{$nid}'");
        }

        // Edges.
        foreach ($topo['edges'] as $e) {
            $eid = (string) ($e['id'] ?? '(unnamed)');
            $from = (string) ($e['from'] ?? '');
            $to = (string) ($e['to'] ?? '');
            if ($eid === '(unnamed)') {
                $this->issues[] = ['level' => 'error', 'message' => "Diagram '{$diagramId}' has an edge with no id."];
            }
            if ($from === '' || !isset($topo['nodes'][$from])) {
                $this->issues[] = ['level' => 'error', 'message' => "Diagram '{$diagramId}' edge '{$eid}' has an unresolved source node: '{$from}'"];
            }
            if ($to === '' || !isset($topo['nodes'][$to])) {
                $this->issues[] = ['level' => 'error', 'message' => "Diagram '{$diagramId}' edge '{$eid}' has an unresolved target node: '{$to}'"];
            }
            $mech = (string) ($e['mechanism'] ?? '');
            if ($mech === '') {
                $this->issues[] = ['level' => 'error', 'message' => "Diagram '{$diagramId}' edge '{$eid}' has no mechanism (every edge must answer 'why are these connected?')."];
            } elseif (!in_array($mech, $mechVocab, true)) {
                $this->issues[] = ['level' => 'error', 'message' => "Diagram '{$diagramId}' edge '{$eid}' uses an out-of-vocabulary mechanism: {$mech}"];
            }
            if (trim((string) ($e['explanation'] ?? '')) === '') {
                $this->issues[] = ['level' => 'error', 'message' => "Diagram '{$diagramId}' edge '{$eid}' is missing a one-sentence explanation."];
            }
            $ver = (string) ($e['verification'] ?? '');
            if ($ver === '') {
                $this->issues[] = ['level' => 'error', 'message' => "Diagram '{$diagramId}' edge '{$eid}' is missing a verification state."];
            } elseif (!in_array($ver, $verVocab, true)) {
                $this->issues[] = ['level' => 'error', 'message' => "Diagram '{$diagramId}' edge '{$eid}' has unknown verification: {$ver}"];
            }
            // An inferred edge must state its basis so the inference is defensible.
            if ($ver !== '' && !edge_verification_is_verified($ver) && trim((string) ($e['basis'] ?? '')) === '') {
                $this->issues[] = ['level' => 'warn', 'message' => "Diagram '{$diagramId}' edge '{$eid}' is inferred but does not state a basis."];
            }
            foreach ($this->toList($e['functionality'] ?? []) as $fn) {
                if (!isset($this->functionality[$fn])) {
                    $this->issues[] = ['level' => 'warn', 'message' => "Diagram '{$diagramId}' edge '{$eid}' references unknown functionality: {$fn}"];
                }
            }
            foreach ($this->toList($e['warnings'] ?? []) as $wn) {
                if (!isset($this->memory['warnings'][$wn])) {
                    $this->issues[] = ['level' => 'warn', 'message' => "Diagram '{$diagramId}' edge '{$eid}' references unknown warning: {$wn}"];
                }
            }
            $checkFiles($e['files'] ?? [], "edge '{$eid}'");
        }

        // SVG markers must map to topology ids, and vice versa, in both
        // directions — so a diagram can never point at a node/edge it can't
        // explain, and every explained node/edge is reachable in the picture.
        $markers = $this->diagramSvgMarkers($svgFile);
        $svgNodes = array_unique($markers['nodes']);
        $svgEdges = array_unique($markers['edges']);
        $topoNodeIds = array_keys($topo['nodes']);
        $topoEdgeIds = array_values(array_filter(array_map(fn ($e) => (string) ($e['id'] ?? ''), $topo['edges'])));

        foreach ($topoNodeIds as $nid) {
            if (!in_array($nid, $svgNodes, true)) {
                $this->issues[] = ['level' => 'error', 'message' => "Diagram '{$diagramId}' node '{$nid}' has no data-vibekb-node marker in the SVG."];
            }
        }
        foreach ($topoEdgeIds as $eid) {
            if (!in_array($eid, $svgEdges, true)) {
                $this->issues[] = ['level' => 'error', 'message' => "Diagram '{$diagramId}' edge '{$eid}' has no data-vibekb-edge marker in the SVG."];
            }
        }
        foreach ($svgNodes as $nid) {
            if (!in_array($nid, $topoNodeIds, true)) {
                $this->issues[] = ['level' => 'error', 'message' => "Diagram '{$diagramId}' SVG marks node '{$nid}' that is not in the topology."];
            }
        }
        foreach ($svgEdges as $eid) {
            if (!in_array($eid, $topoEdgeIds, true)) {
                $this->issues[] = ['level' => 'error', 'message' => "Diagram '{$diagramId}' SVG marks edge '{$eid}' that is not in the topology."];
            }
        }
    }

    // ---- internals -----------------------------------------------------

    /**
     * @return array{meta: array<string,mixed>, html: string, body: string}|null
     */
    private function loadRecord(string $path, string $expectedType): ?array
    {
        $doc = $this->readMarkdownAbsolute($path);
        if ($doc === null) {
            return null;
        }
        $doc['meta']['id'] = $doc['meta']['id'] ?? basename($path, '.md');
        $doc['meta']['type'] = $doc['meta']['type'] ?? $expectedType;
        return $doc;
    }

    /**
     * Load and normalise a diagram's topology JSON (nodes + edges). Structural
     * problems (unsafe name, missing file, bad JSON, unsupported version,
     * duplicate ids) are recorded as issues here; the richer semantic checks
     * (required fields, vocabulary, endpoint resolution, SVG-marker mapping) run
     * in validateTopology() once all content is loaded. A topology that cannot be
     * loaded is simply absent — the diagram still renders without it.
     */
    private function loadTopology(string $diagramId, string $file): void
    {
        $file = basename($file);
        if (!preg_match('/^[a-z0-9\-_.]+\.json$/i', $file) || str_contains($file, '..')) {
            $this->issues[] = ['level' => 'error', 'message' => "Diagram '{$diagramId}' has an unsafe topology filename: {$file}"];
            return;
        }
        $path = $this->root . '/diagrams/topology/' . $file;
        if (!$this->isInsideRoot($path) || !is_file($path)) {
            $this->issues[] = ['level' => 'error', 'message' => "Diagram '{$diagramId}' references a missing topology file: diagrams/topology/{$file}"];
            return;
        }
        $raw = @file_get_contents($path);
        if ($raw === false) {
            $this->issues[] = ['level' => 'error', 'message' => "Diagram '{$diagramId}' topology is unreadable: {$file}"];
            return;
        }
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            $this->issues[] = ['level' => 'error', 'message' => "Diagram '{$diagramId}' topology is not valid JSON: {$file}"];
            return;
        }

        $version = (int) ($data['version'] ?? 0);
        if (!in_array($version, self::TOPOLOGY_VERSIONS, true)) {
            $this->issues[] = ['level' => 'error', 'message' => "Diagram '{$diagramId}' topology has unsupported schema version: " . ($data['version'] ?? '(none)')];
            // Still normalise what we can so the rest of the checks can run.
        }

        $nodes = [];
        foreach ((array) ($data['nodes'] ?? []) as $n) {
            if (!is_array($n)) {
                $this->issues[] = ['level' => 'error', 'message' => "Diagram '{$diagramId}' topology has a malformed node entry."];
                continue;
            }
            $nid = (string) ($n['id'] ?? '');
            if ($nid === '') {
                $this->issues[] = ['level' => 'error', 'message' => "Diagram '{$diagramId}' topology has a node with no id."];
                continue;
            }
            if (isset($nodes[$nid])) {
                $this->issues[] = ['level' => 'error', 'message' => "Diagram '{$diagramId}' topology has a duplicate node id: {$nid}"];
            }
            $nodes[$nid] = $n;
        }

        $edges = [];
        $edgeIds = [];
        foreach ((array) ($data['edges'] ?? []) as $e) {
            if (!is_array($e)) {
                $this->issues[] = ['level' => 'error', 'message' => "Diagram '{$diagramId}' topology has a malformed edge entry."];
                continue;
            }
            $eid = (string) ($e['id'] ?? '');
            if ($eid !== '') {
                if (isset($edgeIds[$eid])) {
                    $this->issues[] = ['level' => 'error', 'message' => "Diagram '{$diagramId}' topology has a duplicate edge id: {$eid}"];
                }
                $edgeIds[$eid] = true;
            }
            $edges[] = $e;
        }

        $this->topology[$diagramId] = [
            'version' => $version,
            'nodes' => $nodes,
            'edges' => array_values($edges),
            'file' => $file,
        ];
    }

    /**
     * @return array{meta: array<string,mixed>, html: string, body: string}|null
     */
    private function readMarkdown(string $relative): ?array
    {
        return $this->readMarkdownAbsolute($this->root . '/' . $relative);
    }

    /**
     * @return array{meta: array<string,mixed>, html: string, body: string}|null
     */
    private function readMarkdownAbsolute(string $path): ?array
    {
        if (!$this->isInsideRoot($path) || !is_file($path)) {
            return null;
        }
        $raw = @file_get_contents($path);
        if ($raw === false) {
            $this->issues[] = ['level' => 'error', 'message' => 'Unreadable file: ' . basename($path)];
            return null;
        }
        $parsed = FrontMatter::parse($raw);
        return [
            'meta' => $parsed['meta'],
            'body' => $parsed['body'],
            'html' => Markdown::toHtml($parsed['body']),
        ];
    }

    /** @return array<string, mixed> */
    private function readJson(string $relative): array
    {
        $path = $this->root . '/' . $relative;
        if (!$this->isInsideRoot($path) || !is_file($path)) {
            return [];
        }
        $raw = @file_get_contents($path);
        if ($raw === false) {
            return [];
        }
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            $this->issues[] = ['level' => 'error', 'message' => "Malformed JSON: {$relative}"];
            return [];
        }
        return $data;
    }

    /** @return list<string> */
    private function safeGlob(string $dir, string $pattern): array
    {
        if (!is_dir($dir) || !$this->isInsideRoot($dir)) {
            return [];
        }
        $matches = glob($dir . '/' . $pattern) ?: [];
        sort($matches);
        return $matches;
    }

    private function isInsideRoot(string $path): bool
    {
        $normalized = str_replace('\\', '/', $path);
        if (str_contains($normalized, '../') || str_contains($normalized, "\0")) {
            return false;
        }
        return str_starts_with($normalized, $this->root . '/') || $normalized === $this->root;
    }

    /**
     * @param mixed $value
     * @return list<string>
     */
    private function toList(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_map('strval', array_filter($value, fn ($v) => (string) $v !== '')));
        }
        if (is_string($value) && $value !== '') {
            return [$value];
        }
        return [];
    }

    private function pluralMemoryType(string $singular): string
    {
        $singular = strtolower(trim($singular));
        return match ($singular) {
            'decision', 'decisions' => 'decisions',
            'constraint', 'constraints' => 'constraints',
            'assumption', 'assumptions' => 'assumptions',
            'warning', 'warnings' => 'warnings',
            'discovery', 'discoveries' => 'discoveries',
            'change', 'changes' => 'changes',
            default => $singular,
        };
    }
}
