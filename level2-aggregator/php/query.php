<?php
declare(strict_types=1);

/**
 * Level 2 RSS query demo in PHP.
 *
 * What this script does:
 *  - loads stored RSS entries from SQLite
 *  - filters them by category and keyword
 *  - prints a JSON preview of the results
 *
 * Usage:
 *  php level2-aggregator/php/query.php --category tech --keyword AI
 *
 * Inputs:
 *  - --db: path to the SQLite database file
 *  - --keyword: optional keyword filter for titles and summaries
 *  - --category: optional category filter
 *  - --limit: maximum number of matching entries to print
 *
 * Outputs:
 *  - JSON text written to the terminal
 */

require_once __DIR__ . '/../../shared/php/db.php';
require_once __DIR__ . '/../../shared/php/query_engine.php';

const DEFAULT_DATABASE_PATH = 'level2-aggregator/db.sqlite';
const DEFAULT_ENTRY_LIMIT = 20;
const ENTRY_FETCH_LIMIT = 500;

/**
 * Read command-line arguments for the query demo.
 *
 * @return array{db: string, keyword: ?string, category: ?string, limit: int}
 */
function buildArguments(): array
{
    $options = getopt(
        '',
        ['db::', 'keyword::', 'category::', 'limit::']
    ) ?: [];

    $databasePath = DEFAULT_DATABASE_PATH;
    if (isset($options['db']) && is_string($options['db'])) {
        $databasePath = $options['db'];
    }

    $keyword = null;
    if (isset($options['keyword']) && is_string($options['keyword'])) {
        $keyword = $options['keyword'];
    }

    $category = null;
    if (isset($options['category']) && is_string($options['category'])) {
        $category = $options['category'];
    }

    $entryLimit = DEFAULT_ENTRY_LIMIT;
    if (isset($options['limit'])) {
        $entryLimit = max(1, (int) $options['limit']);
    }

    return [
        'db' => $databasePath,
        'keyword' => $keyword,
        'category' => $category,
        'limit' => $entryLimit,
    ];
}

/**
 * Run the query tutorial and print matching entries as JSON.
 */
function main(): void
{
    $arguments = buildArguments();

    // Read a larger pool first so the filters have enough data to work with.
    $entries = fetchEntries($arguments['db'], ENTRY_FETCH_LIMIT);

    // Apply the requested filters and trim the final output list.
    $filteredEntries = filterEntries(
        $entries,
        $arguments['keyword'],
        $arguments['category'],
        $arguments['limit']
    );

    echo json_encode($filteredEntries, JSON_PRETTY_PRINT) . PHP_EOL;
}

main();
