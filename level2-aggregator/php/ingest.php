<?php
declare(strict_types=1);

/**
 * Level 2 RSS ingestion demo in PHP.
 *
 * What this script does:
 *  - loads the sample feeds from feeds.yaml
 *  - downloads each RSS feed
 *  - normalizes entries into database-friendly arrays
 *  - stores the entries in SQLite
 *
 * Usage:
 *  php level2-aggregator/php/ingest.php
 *  php level2-aggregator/php/ingest.php --config feeds.yaml
 *  php level2-aggregator/php/ingest.php --db level2-aggregator/db.sqlite
 *
 * Inputs:
 *  - --config: path to the YAML feed configuration file
 *  - --db: path to the SQLite database file
 *
 * Outputs:
 *  - console text showing how many new entries were inserted
 */

require_once __DIR__ . '/../../shared/php/utils.php';
require_once __DIR__ . '/../../shared/php/db.php';

const DEFAULT_CONFIG_PATH = 'feeds.yaml';
const DEFAULT_DATABASE_PATH = 'level2-aggregator/db.sqlite';

/**
 * Read command-line arguments for the ingestion demo.
 *
 * @return array{config: string, db: string}
 */
function buildArguments(): array
{
    $options = getopt('', ['config::', 'db::']) ?: [];

    $configPath = DEFAULT_CONFIG_PATH;
    if (isset($options['config']) && is_string($options['config'])) {
        $configPath = $options['config'];
    }

    $databasePath = DEFAULT_DATABASE_PATH;
    if (isset($options['db']) && is_string($options['db'])) {
        $databasePath = $options['db'];
    }

    return [
        'config' => $configPath,
        'db' => $databasePath,
    ];
}

/**
 * Download one RSS feed and return normalized entries.
 *
 * @return array<int, array<string, string>>
 */
function fetchNormalizedEntries(array $feedDefinition): array
{
    $feedDocument = @simplexml_load_file(
        $feedDefinition['url'],
        'SimpleXMLElement',
        LIBXML_NOCDATA
    );
    if ($feedDocument === false || !isset($feedDocument->channel->item)) {
        return [];
    }

    $normalizedEntries = [];
    foreach ($feedDocument->channel->item as $feedEntry) {
        $normalizedEntries[] = normalizeEntry($feedDefinition, $feedEntry);
    }

    return $normalizedEntries;
}

/**
 * Run the ingestion tutorial.
 */
function main(): void
{
    $arguments = buildArguments();
    $startedAt = gmdate('c');

    try {
        // Load the feed list from the YAML file.
        $feedDefinitions = loadFeedsConfig($arguments['config']);
        if ($feedDefinitions === []) {
            recordIngestRun(
                $arguments['db'],
                [
                    'started_at' => $startedAt,
                    'finished_at' => gmdate('c'),
                    'status' => 'empty',
                    'feed_count' => 0,
                    'entry_count' => 0,
                    'inserted_count' => 0,
                    'message' => 'No feeds configured.',
                ]
            );
            echo "No feeds configured." . PHP_EOL;
            return;
        }

        // Gather entries from each configured feed before writing them to SQLite.
        $allEntries = [];
        foreach ($feedDefinitions as $feedDefinition) {
            $allEntries = array_merge(
                $allEntries,
                fetchNormalizedEntries($feedDefinition)
            );
        }

        $insertedCount = upsertEntries($arguments['db'], $allEntries);
        recordIngestRun(
            $arguments['db'],
            [
                'started_at' => $startedAt,
                'finished_at' => gmdate('c'),
                'status' => 'success',
                'feed_count' => count($feedDefinitions),
                'entry_count' => count($allEntries),
                'inserted_count' => $insertedCount,
                'message' => 'Ingestion completed successfully.',
            ]
        );
        echo "Inserted {$insertedCount} new entries." . PHP_EOL;
    } catch (Throwable $throwable) {
        recordIngestRun(
            $arguments['db'],
            [
                'started_at' => $startedAt,
                'finished_at' => gmdate('c'),
                'status' => 'failed',
                'feed_count' => 0,
                'entry_count' => 0,
                'inserted_count' => 0,
                'message' => $throwable->getMessage(),
            ]
        );
        echo 'Ingestion failed: ' . $throwable->getMessage() . PHP_EOL;
    }
}

main();
