<?php
declare(strict_types=1);

/**
 * Shared utility helpers for the RSS Demo Suite PHP tutorials.
 *
 * What this module does:
 *  - loads the tutorial feed list from feeds.yaml
 *  - flattens the YAML structure into a simple array of feeds
 *  - normalizes RSS entries for database storage
 *
 * Inputs:
 *  - path to the YAML configuration file
 *  - feed metadata and parsed RSS entry objects
 *
 * Outputs:
 *  - an array of feed definitions with category, name, and url fields
 *  - normalized feed entries ready for SQLite storage
 */

/**
 * Load feeds from the tutorial YAML configuration.
 *
 * The parser is intentionally tiny because the demo config is small and
 * predictable. It only supports the structure used by feeds.yaml.
 *
 * @return array<int, array{category: string, name: string, url: string}>
 */
function loadFeedsConfig(string $configPath): array
{
    $configLines = @file($configPath, FILE_IGNORE_NEW_LINES);
    if ($configLines === false) {
        return [];
    }

    $feedDefinitions = [];
    $currentCategory = '';
    $currentFeed = null;

    foreach ($configLines as $configLine) {
        $trimmedLine = trim($configLine);
        if ($trimmedLine === '' || $trimmedLine === 'feeds:') {
            continue;
        }

        if (
            preg_match(
                '/^ {2}([A-Za-z0-9_-]+):$/',
                $configLine,
                $matches
            ) === 1
        ) {
            $currentCategory = $matches[1];
            $currentFeed = null;
            continue;
        }

        if (
            preg_match('/^ {4}- name: (.+)$/', $configLine, $matches) === 1
        ) {
            $currentFeed = [
                'category' => $currentCategory,
                'name' => trim($matches[1]),
                'url' => '',
            ];
            continue;
        }

        if (
            $currentFeed !== null
            && preg_match('/^ {6}url: (.+)$/', $configLine, $matches) === 1
        ) {
            $currentFeed['url'] = trim($matches[1]);
            if ($currentFeed['url'] !== '') {
                $feedDefinitions[] = $currentFeed;
            }
            $currentFeed = null;
        }
    }

    return $feedDefinitions;
}

/**
 * Normalize a parsed RSS entry for SQLite storage.
 *
 * @return array{
 *     feed_name: string,
 *     feed_url: string,
 *     category: string,
 *     title: string,
 *     link: string,
 *     summary: string,
 *     published: string
 * }
 */
function normalizeEntry(
    array $feedDefinition,
    SimpleXMLElement $feedEntry
): array
{
    $published = '';
    $publishedSource = '';
    if (isset($feedEntry->pubDate)) {
        $publishedSource = trim((string) $feedEntry->pubDate);
    } elseif (isset($feedEntry->published)) {
        $publishedSource = trim((string) $feedEntry->published);
    }

    if ($publishedSource !== '') {
        $dateTime = date_create_immutable($publishedSource);
        if ($dateTime !== false) {
            $published = $dateTime
                ->setTimezone(new DateTimeZone('UTC'))
                ->format(DATE_ATOM);
        }
    }

    return [
        'feed_name' => $feedDefinition['name'],
        'feed_url' => $feedDefinition['url'],
        'category' => $feedDefinition['category'],
        'title' => trim((string) ($feedEntry->title ?? '')),
        'link' => trim((string) ($feedEntry->link ?? '')),
        'summary' => trim((string) ($feedEntry->description ?? '')),
        'published' => $published,
    ];
}
