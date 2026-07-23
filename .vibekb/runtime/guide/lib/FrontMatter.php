<?php

declare(strict_types=1);

/**
 * Front matter parser for VibeKB content records.
 *
 * Deliberately dependency-free (no ext-yaml) so it runs on ordinary PHP 8.2
 * shared hosting. Supports the small subset of YAML the content model needs:
 *
 *   key: scalar value          -> string | int | bool | null
 *   key: "quoted value"        -> string (quotes stripped)
 *   key: [a, b, c]             -> list<string>
 *   key: []                    -> []
 *   key:                       -> followed by "- item" lines (block list)
 *     - item
 *     - item
 *
 * Anything more exotic is intentionally unsupported; keep records simple.
 */
final class FrontMatter
{
    /**
     * @return array{meta: array<string, mixed>, body: string}
     */
    public static function parse(string $raw): array
    {
        $raw = preg_replace("/^\xEF\xBB\xBF/", '', $raw) ?? $raw;
        if (!preg_match('/\A---\r?\n(.*?)\r?\n---\r?\n?(.*)\z/s', $raw, $matches)) {
            return ['meta' => [], 'body' => trim($raw)];
        }

        $meta = [];
        $lines = preg_split('/\r?\n/', $matches[1]) ?: [];
        $count = count($lines);

        for ($i = 0; $i < $count; $i++) {
            $line = $lines[$i];
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                continue;
            }
            if (!preg_match('/^([A-Za-z0-9_\-]+)\s*:\s*(.*)$/', $trimmed, $m)) {
                continue;
            }
            $key = $m[1];
            $value = trim($m[2]);

            if ($value === '') {
                // Possible block list on following indented "- " lines.
                $items = [];
                while ($i + 1 < $count && preg_match('/^\s*-\s+(.*)$/', $lines[$i + 1], $lm)) {
                    $items[] = self::castScalar(trim($lm[1]));
                    $i++;
                }
                $meta[$key] = $items === [] ? '' : $items;
                continue;
            }

            if (preg_match('/^\[(.*)\]$/s', $value, $am)) {
                $meta[$key] = self::parseInlineList($am[1]);
                continue;
            }

            $meta[$key] = self::castScalar($value);
        }

        return ['meta' => $meta, 'body' => trim($matches[2])];
    }

    /**
     * @return list<string>
     */
    private static function parseInlineList(string $inner): array
    {
        $inner = trim($inner);
        if ($inner === '') {
            return [];
        }
        $items = [];
        foreach (explode(',', $inner) as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }
            $value = self::castScalar($part);
            $items[] = is_string($value) ? $value : (string) $value;
        }
        return $items;
    }

    private static function castScalar(string $value): mixed
    {
        if ($value === '') {
            return '';
        }
        if (preg_match('/^"(.*)"$/s', $value, $m) || preg_match("/^'(.*)'$/s", $value, $m)) {
            return $m[1];
        }
        $lower = strtolower($value);
        if ($lower === 'true') {
            return true;
        }
        if ($lower === 'false') {
            return false;
        }
        if ($lower === 'null') {
            return null;
        }
        if (preg_match('/^-?\d+$/', $value)) {
            return (int) $value;
        }
        return $value;
    }
}
