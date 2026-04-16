<?php
declare(strict_types=1);

/**
 * Simple query helpers for the PHP Level 2 tutorial.
 *
 * What this module does:
 *  - filters stored RSS entries by category
 *  - filters stored RSS entries by keyword
 *  - trims the final list to a requested limit
 */

/**
 * Filter a list of entries by keyword and/or category.
 *
 * @param array<int, array<string, string>> $entries
 * @return array<int, array<string, string>>
 */
function filterEntries(
    array $entries,
    ?string $keyword = null,
    ?string $category = null,
    ?int $limit = null
): array {
    $filteredEntries = $entries;

    if ($category !== null && $category !== '') {
        $categoryLower = strtolower($category);
        $filteredEntries = array_values(
            array_filter(
                $filteredEntries,
                static function (array $entry) use ($categoryLower): bool {
                    return strtolower((string) ($entry['category'] ?? '')) ===
                        $categoryLower;
                }
            )
        );
    }

    if ($keyword !== null && $keyword !== '') {
        $keywordLower = strtolower($keyword);
        $filteredEntries = array_values(
            array_filter(
                $filteredEntries,
                static function (array $entry) use ($keywordLower): bool {
                    $title = strtolower((string) ($entry['title'] ?? ''));
                    $summary = strtolower((string) ($entry['summary'] ?? ''));
                    return str_contains($title, $keywordLower)
                        || str_contains($summary, $keywordLower);
                }
            )
        );
    }

    if ($limit !== null) {
        return array_slice($filteredEntries, 0, $limit);
    }

    return $filteredEntries;
}
