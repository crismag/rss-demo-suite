<?php
declare(strict_types=1);

/**
 * Hacker News RSS sample page.
 *
 * What this page does:
 *  - fetches the official Hacker News RSS feed
 *  - shows a featured story and a compact list of recent stories
 *  - stays readable on mobile and desktop screens
 *
 * Inputs:
 *  - the live RSS feed at https://news.ycombinator.com/rss
 *
 * Outputs:
 *  - a responsive HTML page with Hacker News stories
 */

const HN_FEED_URL = 'https://news.ycombinator.com/rss';

function escapeHtml(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function formatTimestamp(string $timestamp): string
{
    if ($timestamp === '') {
        return 'Not recorded yet';
    }

    try {
        $dateTime = new DateTimeImmutable($timestamp);
        return $dateTime->format('M j, Y');
    } catch (Throwable $exception) {
        return $timestamp;
    }
}

function loadFeed(string $feedUrl): ?SimpleXMLElement
{
    $feed = @simplexml_load_file($feedUrl, 'SimpleXMLElement', LIBXML_NOCDATA);
    if ($feed === false) {
        return null;
    }

    return $feed;
}

function extractLink(SimpleXMLElement $item): string
{
    if (isset($item->link)) {
        return trim((string) $item->link);
    }

    return HN_FEED_URL;
}

/**
 * @return array<int, array{title: string, summary: string, published: string, link: string}>
 */
function buildStories(SimpleXMLElement $feed): array
{
    $stories = [];
    foreach ($feed->channel->item as $item) {
        $stories[] = [
            'title' => trim((string) ($item->title ?? '(no title)')),
            'summary' => trim(strip_tags((string) ($item->description ?? ''))),
            'published' => trim((string) ($item->pubDate ?? '')),
            'link' => extractLink($item),
        ];
    }

    return array_slice($stories, 0, 12);
}

$feed = loadFeed(HN_FEED_URL);
$stories = $feed instanceof SimpleXMLElement ? buildStories($feed) : [];
$featuredStory = $stories[0] ?? [
    'title' => 'Unable to load Hacker News right now',
    'summary' => 'The feed could not be fetched at the moment.',
    'published' => '',
    'link' => HN_FEED_URL,
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hacker News Sample</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <main class="page-shell">
        <section class="hero">
            <p class="eyebrow">Source-Specific Sample</p>
            <h1>Hacker News RSS</h1>
            <p class="lead">
                This view keeps the feed focused on headline reading. It is
                designed to be quick on phones and easy to skim on larger
                screens.
            </p>
            <div class="hero-meta">
                <span class="chip">Source: Hacker News</span>
                <span class="chip">Official RSS feed</span>
                <span class="chip">Mobile-friendly cards</span>
            </div>
        </section>

        <section class="feature panel">
            <div>
                <p class="card-label">Featured story</p>
                <h2><?= escapeHtml($featuredStory['title']) ?></h2>
                <p class="muted"><?= escapeHtml(formatTimestamp($featuredStory['published'])) ?></p>
                <p class="story-summary"><?= escapeHtml($featuredStory['summary']) ?></p>
                <a class="button" href="<?= escapeHtml($featuredStory['link']) ?>" target="_blank" rel="noopener noreferrer">
                    Open story
                </a>
            </div>
            <div class="feature-badge">
                <span>HN</span>
            </div>
        </section>

        <section class="story-grid">
            <?php foreach ($stories as $index => $story): ?>
                <article class="story-card">
                    <p class="story-index">#<?= $index + 1 ?></p>
                    <a class="story-title" href="<?= escapeHtml($story['link']) ?>" target="_blank" rel="noopener noreferrer">
                        <?= escapeHtml($story['title']) ?>
                    </a>
                    <p class="story-meta"><?= escapeHtml(formatTimestamp($story['published'])) ?></p>
                    <p class="story-summary"><?= escapeHtml($story['summary']) ?></p>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="panel notes">
            <article class="note-card">
                <h3>What To Notice</h3>
                <p>
                    This page does not need images to feel useful. It focuses on
                    headline density, touch-friendly spacing, and quick scanning.
                </p>
            </article>

            <article class="note-card">
                <h3>What Else Can Be Added</h3>
                <p>
                    You can add a comments view, an author filter, or a second
                    feed for Ask HN stories later.
                </p>
            </article>
        </section>
    </main>
</body>
</html>
