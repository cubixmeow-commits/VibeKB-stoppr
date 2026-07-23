<?php

declare(strict_types=1);

/**
 * Search-index builder. Produces the flat list of entries the client-side
 * search reads. Shared by both output modes:
 *
 *   - the dynamic guide serves it live at `?view=search&data=json`;
 *   - the static generator writes it to `assets/data/search.json`.
 *
 * URLs are produced through the supplied {@see UrlStrategy}, so each mode gets
 * links that resolve from the search page's location. The index only ever
 * contains public VibeKB guide content (titles, summaries, and record bodies) —
 * never secrets, raw config, or anything the guide itself does not already
 * publish.
 *
 * @return list<array{title:string,summary:string,type:string,url:string,body:string}>
 */
function build_search_index(Content $content, UrlStrategy $strategy): array
{
    $index = [];

    $plain = static function (string $markdown): string {
        // Reduce a Markdown body to a searchable plain string.
        $text = preg_replace('/```.*?```/s', ' ', $markdown) ?? $markdown;
        $text = preg_replace('/[#>*_`\-\|\[\]\(\)]+/', ' ', $text) ?? $text;
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;
        $text = trim($text);
        return mb_substr($text, 0, 800);
    };

    // Functionality records.
    foreach ($content->allFunctionality() as $id => $rec) {
        $m = $rec['meta'];
        $index[] = [
            'title' => (string) ($m['title'] ?? $id),
            'summary' => (string) ($m['summary'] ?? ''),
            'type' => 'functionality',
            'url' => $strategy->functionality((string) $id),
            'body' => $plain((string) ($rec['body'] ?? '')),
        ];
    }

    // Functional areas.
    foreach ($content->functionalityGroups() as $group) {
        $index[] = [
            'title' => (string) $group['title'],
            'summary' => (string) $group['description'],
            'type' => 'functional area',
            'url' => $strategy->view('functionality', ['area' => (string) $group['id']]),
            'body' => '',
        ];
    }

    // Diagrams — the record itself, then each explainable node and edge so a
    // reader can search node titles/purposes, edge labels/explanations, and the
    // files (with their reasons) and land on the exact node/edge anchor.
    $diagramsBase = $strategy->diagram('');
    foreach ($content->allDiagrams() as $id => $rec) {
        $m = $rec['meta'];
        $index[] = [
            'title' => (string) ($m['title'] ?? $id),
            'summary' => (string) ($m['summary'] ?? ''),
            'type' => 'diagram',
            'url' => $strategy->diagram((string) $id),
            'body' => $plain((string) ($rec['body'] ?? '')),
        ];

        $topo = $content->resolvedTopology((string) $id);
        if ($topo === null) {
            continue;
        }
        $fileBody = static function (array $files): string {
            $parts = [];
            foreach ($files as $f) {
                $parts[] = trim((string) ($f['path'] ?? '') . ' ' . (string) ($f['reason'] ?? ''));
            }
            return trim(implode(' · ', array_filter($parts)));
        };
        foreach ($topo['nodes'] as $nid => $node) {
            $index[] = [
                'title' => (string) $node['title'],
                'summary' => (string) $node['purpose'],
                'type' => 'diagram node',
                'url' => $diagramsBase . '#node-' . $nid,
                'body' => $fileBody($node['files']),
            ];
        }
        foreach ($topo['edges'] as $edge) {
            $label = (string) ($edge['label'] ?? '');
            $index[] = [
                'title' => (string) $edge['from_title'] . ' → ' . (string) $edge['to_title']
                    . ($label !== '' ? ' (' . $label . ')' : ''),
                'summary' => (string) $edge['explanation'],
                'type' => 'diagram connection',
                'url' => $diagramsBase . '#edge-' . (string) $edge['id'],
                'body' => trim((string) $edge['basis'] . ' ' . $fileBody($edge['files'])),
            ];
        }
    }

    // Repository memory (decisions, constraints, warnings, ...).
    foreach ($content->memory() as $type => $records) {
        $singular = rtrim($type, 's');
        foreach ($records as $id => $rec) {
            $m = $rec['meta'];
            $index[] = [
                'title' => (string) ($m['title'] ?? $id),
                'summary' => (string) ($m['summary'] ?? ''),
                'type' => $singular === 'discoverie' ? 'discovery' : $singular,
                'url' => $strategy->memory((string) $type, (string) $id),
                'body' => $plain((string) ($rec['body'] ?? '')),
            ];
        }
    }

    // Important files.
    foreach ($content->files() as $file) {
        $path = (string) ($file['path'] ?? '');
        if ($path === '') {
            continue;
        }
        $index[] = [
            'title' => $path,
            'summary' => (string) ($file['purpose'] ?? ''),
            'type' => 'file',
            'url' => $strategy->view('files'),
            'body' => '',
        ];
    }

    // System documents (architecture, data & storage).
    foreach (['mental-model', 'components', 'request-flow', 'data-flow', 'storage', 'deployment'] as $name) {
        $doc = $content->systemDoc($name);
        if ($doc === null) {
            continue;
        }
        $isStorage = in_array($name, ['storage', 'data-flow'], true);
        $index[] = [
            'title' => (string) ($doc['meta']['title'] ?? ucfirst(str_replace('-', ' ', $name))),
            'summary' => (string) ($doc['meta']['summary'] ?? ''),
            'type' => $isStorage ? 'data & storage' : 'architecture',
            'url' => $strategy->view($isStorage ? 'data' : 'how-it-works'),
            'body' => $plain((string) ($doc['body'] ?? '')),
        ];
    }

    // Work records.
    $work = $content->currentWork();
    if ($work !== null) {
        $index[] = [
            'title' => (string) ($work['meta']['title'] ?? 'Current work'),
            'summary' => (string) ($work['meta']['summary'] ?? ''),
            'type' => 'current work',
            'url' => $strategy->view('current-work'),
            'body' => $plain((string) ($work['body'] ?? '')),
        ];
    }
    $handoff = $content->handoff();
    if ($handoff !== null) {
        $index[] = [
            'title' => (string) ($handoff['meta']['title'] ?? 'Handoff'),
            'summary' => (string) ($handoff['meta']['summary'] ?? ''),
            'type' => 'handoff',
            'url' => $strategy->view('handoff'),
            'body' => $plain((string) ($handoff['body'] ?? '')),
        ];
    }

    return $index;
}
