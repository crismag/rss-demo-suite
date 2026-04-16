<?php
declare(strict_types=1);

/**
 * Bible Gateway Verse of the Day sample page.
 *
 * What this page does:
 *  - fetches the Verse of the Day Atom feed
 *  - displays the verse in a mobile-friendly card
 *  - shows the date, title, and source link
 *
 * Inputs:
 *  - the live Atom feed at https://www.biblegateway.com/votd/get/?format=atom
 *
 * Outputs:
 *  - a styled HTML page with the current verse content
 */

const BIBLE_VOTD_FEED_URL = 'https://www.biblegateway.com/votd/get/?format=atom';

/**
 * Escape text for safe HTML output.
 */
function escapeHtml(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Format an ISO date for display.
 */
function formatTimestamp(string $timestamp): string
{
    if ($timestamp === '') {
        return 'Not recorded yet';
    }

    try {
        $dateTime = new DateTimeImmutable($timestamp);
        return $dateTime->format('F j, Y');
    } catch (Throwable $exception) {
        return $timestamp;
    }
}

/**
 * Load the Atom feed into a SimpleXML object.
 */
function loadFeed(string $feedUrl): ?SimpleXMLElement
{
    $feed = @simplexml_load_file($feedUrl, 'SimpleXMLElement', LIBXML_NOCDATA);
    if ($feed === false) {
        return null;
    }

    return $feed;
}

/**
 * Extract the first Atom entry link from a feed entry.
 */
function extractEntryLink(SimpleXMLElement $entry): string
{
    foreach ($entry->link as $linkNode) {
        $attributes = $linkNode->attributes();
        $href = (string) ($attributes['href'] ?? '');
        if ($href !== '') {
            return $href;
        }
    }

    return '';
}

/**
 * Convert a feed entry into a simple view model.
 *
 * @return array{title: string, summary: string, updated: string, link: string}
 */
function buildVerseModel(SimpleXMLElement $feed): array
{
    $entry = $feed->entry[0] ?? null;
    if (!$entry instanceof SimpleXMLElement) {
        return [
            'title' => 'No verse available',
            'summary' => 'The feed returned no entries.',
            'updated' => '',
            'link' => BIBLE_VOTD_FEED_URL,
        ];
    }

    return [
        'title' => trim((string) ($entry->title ?? 'Bible Gateway Verse of the Day')),
        'summary' => trim(strip_tags((string) ($entry->summary ?? ''))),
        'updated' => trim((string) ($entry->updated ?? '')),
        'link' => extractEntryLink($entry) ?: BIBLE_VOTD_FEED_URL,
    ];
}

$feed = loadFeed(BIBLE_VOTD_FEED_URL);
$hasFeed = $feed instanceof SimpleXMLElement;
$verse = $hasFeed ? buildVerseModel($feed) : [
    'title' => 'Unable to load the Verse of the Day',
    'summary' => 'The page could not fetch the live Atom feed right now.',
    'updated' => '',
    'link' => BIBLE_VOTD_FEED_URL,
];
$feedTitle = $hasFeed ? trim((string) ($feed->title ?? 'Bible Gateway Verse of the Day')) : 'Bible Gateway Verse of the Day';
$feedSubtitle = $hasFeed ? trim((string) ($feed->subtitle ?? '')) : 'Daily Scripture from Bible Gateway';
$entryDate = formatTimestamp($verse['updated']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verse of the Day</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <main class="page-shell">
        <section class="hero">
            <p class="eyebrow">Source-Specific Sample</p>
            <h1><?= escapeHtml($feedTitle) ?></h1>
            <p class="lead">
                <?= escapeHtml($feedSubtitle !== '' ? $feedSubtitle : 'Daily Scripture from Bible Gateway') ?>
            </p>
            <div class="hero-meta">
                <span class="chip">Source: Bible Gateway</span>
                <span class="chip">Live Atom feed</span>
                <span class="chip"><?= escapeHtml($entryDate !== 'Not recorded yet' ? $entryDate : 'Waiting for update') ?></span>
            </div>
        </section>

        <section class="verse-card">
            <div class="verse-header">
                <p class="card-label">Verse of the Day</p>
                <a class="source-link" href="<?= escapeHtml($verse['link']) ?>" target="_blank" rel="noopener noreferrer">
                    Open source
                </a>
            </div>

            <h2><?= escapeHtml($verse['title']) ?></h2>
            <p class="verse-date">
                Updated: <?= escapeHtml($entryDate) ?>
            </p>
            <blockquote class="verse-text">
                <?= escapeHtml($verse['summary']) ?>
            </blockquote>
            <div class="verse-actions">
                <a class="button" href="<?= escapeHtml($verse['link']) ?>" target="_blank" rel="noopener noreferrer">
                    Read on Bible Gateway
                </a>
            </div>
        </section>

        <section class="notes">
            <article class="note-card">
                <h3>What To Notice</h3>
                <p>
                    The verse is displayed as a focused reading card so it stays
                    comfortable on phones and easy to scan on larger screens.
                </p>
            </article>

            <article class="note-card">
                <h3>What Else Can Be Added</h3>
                <p>
                    You can add caching, previous verses, or a second card that
                    compares the current verse with the last stored entry.
                </p>
            </article>
        </section>
    </main>
</body>
</html>
