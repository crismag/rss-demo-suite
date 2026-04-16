<?php
declare(strict_types=1);

/**
 * Level 1 RSS parsing demo in PHP.
 *
 * What this script does:
 *  - loads the feed configuration from feeds.yaml
 *  - selects the first configured feed
 *  - downloads and parses that RSS feed
 *  - prints a small set of entry titles to the terminal
 *
 * Usage:
 *  php level1-basic/php/demo.php
 *  php level1-basic/php/demo.php --config feeds.yaml --limit 5
 *
 * Inputs:
 *  - --config: path to the YAML feed configuration file
 *  - --limit: maximum number of feed titles to display
 *
 * Outputs:
 *  - console text showing the feed name, category, and entry titles
 */

require_once __DIR__ . '/../../shared/php/utils.php';

const DEFAULT_CONFIG_PATH = 'feeds.yaml';
const DEFAULT_ENTRY_LIMIT = 5;

/**
 * Read command-line arguments for the tutorial script.
 *
 * Purpose:
 *  Keep the demo's command line small and easy to scan.
 *
 * Expected input:
 *  Optional ``--config`` and ``--limit`` arguments.
 *
 * Minimal example:
 *  ``php level1-basic/php/demo.php --limit 3``
 *
 * Output:
 *  An associative array with the config path and entry limit.
 *
 * Things to observe:
 *  The defaults make the demo runnable with no arguments.
 *
 * @return array{config: string, limit: int}
 */
function buildArguments(): array
{
    $options = getopt('', ['config::', 'limit::']) ?: [];

    $configPath = DEFAULT_CONFIG_PATH;
    if (isset($options['config']) && is_string($options['config'])) {
        $configPath = $options['config'];
    }

    $entryLimit = DEFAULT_ENTRY_LIMIT;
    if (isset($options['limit'])) {
        $entryLimit = max(1, (int) $options['limit']);
    }

    return [
        'config' => $configPath,
        'limit' => $entryLimit,
    ];
}

/**
 * Download one RSS feed and return the titles of its entries.
 *
 * Purpose:
 *  Fetch RSS XML and extract a short list of readable titles.
 *
 * Expected input:
 *  A feed URL and the maximum number of titles to return.
 *
 * Minimal example:
 *  ``fetchEntryTitles("https://hnrss.org/frontpage", 2)``
 *
 * Output:
 *  A plain array of title strings, or an empty array if the feed is not
 *  readable.
 *
 * Things to observe:
 *  This helper keeps the RSS parsing logic separate from printing.
 *
 * @return array<int, string>
 */
function fetchEntryTitles(string $feedUrl, int $entryLimit): array
{
    $feedDocument = @simplexml_load_file(
        $feedUrl,
        'SimpleXMLElement',
        LIBXML_NOCDATA
    );
    if ($feedDocument === false || !isset($feedDocument->channel->item)) {
        return [];
    }

    $entryTitles = [];
    foreach ($feedDocument->channel->item as $feedItem) {
        $entryTitle = trim((string) $feedItem->title);
        $entryTitles[] = $entryTitle !== '' ? $entryTitle : '(no title)';

        if (count($entryTitles) >= $entryLimit) {
            break;
        }
    }

    return $entryTitles;
}

/**
 * Print a short preview for the selected feed.
 *
 * Purpose:
 *  Show the selected feed name and the first few RSS item titles.
 *
 * Expected input:
 *  A flattened feed definition from ``loadFeedsConfig()``.
 *
 * Minimal example:
 *  ``printFeedPreview($selectedFeed, 5)``
 *
 * Output:
 *  A short terminal preview with the feed label and numbered titles.
 *
 * Things to observe:
 *  A feed with no readable items prints a friendly message instead of
 *  failing.
 */
function printFeedPreview(array $feedDefinition, int $entryLimit): void
{
    // Show which feed was selected so the output stays easy to follow.
    $feedName = $feedDefinition['name'];
    $feedCategory = $feedDefinition['category'];
    echo "Feed: {$feedName} ({$feedCategory})" . PHP_EOL;

    // Print a short list of titles to demonstrate basic RSS access.
    $entryTitles = fetchEntryTitles($feedDefinition['url'], $entryLimit);
    if ($entryTitles === []) {
        echo "No entries found." . PHP_EOL;
        return;
    }

    foreach ($entryTitles as $entryTitle) {
        echo "- {$entryTitle}" . PHP_EOL;
    }
}

/**
 * Run the basic RSS parsing tutorial script.
 *
 * Purpose:
 *  Connect the tutorial pieces: config loading, feed selection, and output.
 *
 * Expected input:
 *  Command-line arguments from the user, or no arguments at all.
 *
 * Minimal example:
 *  ``php level1-basic/php/demo.php``
 *
 * Output:
 *  A short RSS preview printed to the terminal.
 *
 * Things to observe:
 *  The first configured feed is used so the workflow stays simple.
 */
function main(): void
{
    $arguments = buildArguments();

    // Load the feed list from the YAML file.
    $feedDefinitions = loadFeedsConfig($arguments['config']);
    if ($feedDefinitions === []) {
        echo "No feeds configured." . PHP_EOL;
        return;
    }

    // Keep the demo focused by using the first configured feed only.
    $selectedFeed = $feedDefinitions[0];
    printFeedPreview($selectedFeed, $arguments['limit']);
}

main();
