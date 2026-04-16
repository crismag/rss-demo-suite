<?php
declare(strict_types=1);

use SimplePie\SimplePie;

/**
 * Level 1 RSS parsing demo using SimplePie and a real-world feed.
 *
 * What this script does:
 *  - selects a preset RSS source or a custom RSS URL
 *  - downloads and parses that RSS feed with SimplePie
 *  - prints a tiny preview of the entry titles
 *
 * Usage:
 *  php level1-basic/php/demo_realworld.php
 *  php level1-basic/php/demo_realworld.php --source hackernews
 *  php level1-basic/php/demo_realworld.php --url https://example.com/feed
 *  php level1-basic/php/demo_realworld.php --limit 5
 *
 * Inputs:
 *  - --source: choose a preset RSS source
 *  - --url: override the feed URL with a custom RSS source
 *  - --limit: maximum number of entry titles to display
 *
 * Outputs:
 *  - console text showing the selected feed and a short title preview
 *
 * Things to observe:
 *  - the default source is The Daily WTF
 *  - a custom URL can override the preset list
 *  - the output is numbered so it is easier to scan
 */

$composerAutoloadPath = __DIR__ . '/../../vendor/autoload.php';
if (file_exists($composerAutoloadPath)) {
    require_once $composerAutoloadPath;
}

const DEFAULT_SOURCE_KEY = "dailywtf";
const DEFAULT_ENTRY_LIMIT = 2;

const RSS_SOURCES = [
    "dailywtf" => [
        "name" => "The Daily WTF",
        "url" => "https://feeds.feedburner.com/TheDailyWtf",
    ],
    "hackernews" => [
        "name" => "Hacker News",
        "url" => "https://hnrss.org/frontpage",
    ],
    "espn" => [
        "name" => "ESPN Top Headlines",
        "url" => "https://www.espn.com/espn/rss/news",
    ],
];

/**
 * Read command-line arguments for the real-world tutorial script.
 *
 * Purpose:
 *  Keep the demo's interface small and easy to read.
 *
 * Expected input:
 *  Optional ``--source``, ``--url``, and ``--limit`` arguments.
 *
 * Minimal example:
 *  ``php level1-basic/php/demo_realworld.php --source hackernews``
 *
 * Output:
 *  An associative array with the selected source key, custom URL, and limit.
 *
 * Things to observe:
 *  The defaults keep the first run short and beginner-friendly.
 *
 * @return array{source: string, url: ?string, limit: int}
 */
function buildArguments(): array
{
    $options = getopt('', ['source::', 'url::', 'limit::']) ?: [];

    $sourceKey = DEFAULT_SOURCE_KEY;
    if (isset($options['source']) && is_string($options['source'])) {
        $sourceKey = $options['source'];
    }

    $customUrl = null;
    if (isset($options['url']) && is_string($options['url'])) {
        $customUrl = $options['url'];
    }

    $entryLimit = DEFAULT_ENTRY_LIMIT;
    if (isset($options['limit'])) {
        $entryLimit = max(1, (int) $options['limit']);
    }

    return [
        'source' => $sourceKey,
        'url' => $customUrl,
        'limit' => $entryLimit,
    ];
}

/**
 * Resolve the selected source label and URL.
 *
 * Purpose:
 *  Turn a preset source key or custom URL into a readable label and feed URL.
 *
 * Expected input:
 *  - ``$sourceKey`` should match one of the keys in ``RSS_SOURCES``
 *  - ``$customUrl`` may be ``null`` or a direct RSS feed URL
 *
 * Minimal example:
 *  ``resolveSource("dailywtf", null)``
 *
 * Output:
 *  A two-item array containing the feed name and feed URL.
 *
 * Things to observe:
 *  A custom URL overrides the preset list and keeps the demo flexible.
 *
 * @return array{0: string, 1: string}
 */
function resolveSource(string $sourceKey, ?string $customUrl): array
{
    if ($customUrl !== null && $customUrl !== '') {
        return ['Custom source', $customUrl];
    }

    $selectedSource = RSS_SOURCES[$sourceKey];
    return [$selectedSource['name'], $selectedSource['url']];
}

/**
 * Print a banner so the preview is easy to scan.
 */
function printFeedBanner(string $feedName, string $feedUrl): void
{
    echo str_repeat('=', 50) . PHP_EOL;
    echo "Feed: {$feedName}" . PHP_EOL;
    echo "URL: {$feedUrl}" . PHP_EOL;
    echo str_repeat('=', 50) . PHP_EOL;
}

/**
 * Print a closing separator after the preview.
 */
function printFeedFooter(): void
{
    echo str_repeat('=', 50) . PHP_EOL;
}

/**
 * Download one feed and print a simple preview of its entries.
 *
 * Purpose:
 *  Fetch RSS XML with SimplePie and print the first few entry titles.
 *
 * Expected input:
 *  A label, a feed URL, and a small entry limit.
 *
 * Minimal example:
 *  ``printFeedPreview("The Daily WTF", "https://...", 2)``
 *
 * Output:
 *  A simple terminal preview with a header, numbered items, and a footer.
 *
 * Things to observe:
 *  - the script handles missing SimplePie gracefully
 *  - parsing warnings are shown instead of throwing a fatal error
 *  - the preview stays short so the output is easy to read
 *
 * Developer note:
 *  The banner, parsing, and error handling are kept in one place for clarity.
 */
function printFeedPreview(
    string $feedName,
    string $feedUrl,
    int $entryLimit
): void
{
    printFeedBanner($feedName, $feedUrl);

    if (!class_exists(SimplePie::class)) {
        echo "Missing dependency: simplepie/simplepie" . PHP_EOL;
        echo "Install it with: composer require simplepie/simplepie" . PHP_EOL;
        echo "If you use Composer, make sure vendor/autoload.php exists." .
            PHP_EOL;
        printFeedFooter();
        return;
    }

    $feedReader = new SimplePie();
    $feedReader->set_feed_url($feedUrl);
    $feedReader->enable_cache(false);

    if (!$feedReader->init()) {
        $feedErrors = $feedReader->get_errors();
        if (is_array($feedErrors) && $feedErrors !== []) {
            foreach ($feedErrors as $feedError) {
                echo "Feed parsing warning: {$feedError}" . PHP_EOL;
            }
        } else {
            echo "Could not read feed." . PHP_EOL;
        }
        printFeedFooter();
        return;
    }

    $feedReader->handle_content_type();
    $feedItems = $feedReader->get_items(0, $entryLimit) ?: [];

    if ($feedItems === []) {
        echo "No entries found." . PHP_EOL;
        printFeedFooter();
        return;
    }

    foreach ($feedItems as $index => $feedItem) {
        $entryTitle = $feedItem->get_title();
        if ($entryTitle === null || $entryTitle === '') {
            $entryTitle = '(no title)';
        }
        echo ($index + 1) . '. ' . $entryTitle . PHP_EOL;
    }

    printFeedFooter();
}

/**
 * Run the real-world RSS tutorial script.
 *
 * Purpose:
 *  Glue together argument parsing, source selection, and preview output.
 *
 * Expected input:
 *  Command-line options from the user, such as ``--source`` or ``--url``.
 *
 * Minimal example:
 *  ``php level1-basic/php/demo_realworld.php``
 *
 * Output:
 *  A formatted preview of RSS entry titles, or a friendly error message.
 *
 * Things to observe:
 *  This function keeps the top-level flow short so the demo is easy to trace.
 */
function main(): void
{
    $arguments = buildArguments();
    [$feedName, $feedUrl] = resolveSource(
        $arguments['source'],
        $arguments['url']
    );
    printFeedPreview($feedName, $feedUrl, $arguments['limit']);
}

main();
