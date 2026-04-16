<?php
declare(strict_types=1);

/**
 * ESPN multi-view RSS sample page.
 *
 * What this page does:
 *  - fetches a few ESPN RSS feeds server-side
 *  - shows a featured story, feed cards, and a compact headline table
 *  - tries to display article images when the feed provides them
 *
 * Inputs:
 *  - the ESPN RSS feed URLs configured below
 *
 * Outputs:
 *  - a responsive HTML dashboard with feed summaries and article cards
 */

const ESPN_FEEDS = [
    [
        'label' => 'Top Headlines',
        'url' => 'https://www.espn.com/espn/rss/news',
        'accent' => 'accent-gold',
    ],
    [
        'label' => 'NFL',
        'url' => 'https://www.espn.com/espn/rss/nfl/news',
        'accent' => 'accent-blue',
    ],
    [
        'label' => 'NBA',
        'url' => 'https://www.espn.com/espn/rss/nba/news',
        'accent' => 'accent-red',
    ],
    [
        'label' => 'Soccer',
        'url' => 'https://www.espn.com/espn/rss/soccer/news',
        'accent' => 'accent-green',
    ],
];

/**
 * Escape text for safe HTML output.
 */
function escapeHtml(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Format a published date for display.
 */
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

/**
 * Load an RSS feed as SimpleXML.
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
 * Extract a likely article image from an RSS item.
 */
function extractItemImage(SimpleXMLElement $item): string
{
    $namespaces = $item->getNamespaces(true);

    if (isset($namespaces['media'])) {
        $media = $item->children($namespaces['media']);
        if (isset($media->thumbnail)) {
            $attributes = $media->thumbnail->attributes();
            $thumbnailUrl = (string) ($attributes['url'] ?? '');
            if ($thumbnailUrl !== '') {
                return $thumbnailUrl;
            }
        }

        if (isset($media->content)) {
            $attributes = $media->content->attributes();
            $contentUrl = (string) ($attributes['url'] ?? '');
            if ($contentUrl !== '') {
                return $contentUrl;
            }
        }
    }

    if (isset($item->enclosure)) {
        $attributes = $item->enclosure->attributes();
        $enclosureUrl = (string) ($attributes['url'] ?? '');
        if ($enclosureUrl !== '') {
            return $enclosureUrl;
        }
    }

    return '';
}

/**
 * Extract the first useful link from an RSS item.
 */
function extractItemLink(SimpleXMLElement $item): string
{
    foreach ($item->link as $linkNode) {
        $attributes = $linkNode->attributes();
        $href = (string) ($attributes['href'] ?? '');
        if ($href !== '') {
            return $href;
        }
    }

    return (string) ($item->link ?? '');
}

/**
 * Turn one RSS item into a plain array for rendering.
 *
 * @return array<string, string>
 */
function buildItemViewModel(SimpleXMLElement $item, string $feedLabel): array
{
    return [
        'feed' => $feedLabel,
        'title' => trim((string) ($item->title ?? '(no title)')),
        'summary' => trim(strip_tags((string) ($item->description ?? $item->summary ?? ''))),
        'published' => trim((string) ($item->pubDate ?? $item->updated ?? '')),
        'link' => extractItemLink($item),
        'image' => extractItemImage($item),
    ];
}

/**
 * Load a feed and return a small list of item view models.
 *
 * @return array{label: string, url: string, accent: string, items: array<int, array<string, string>>, ok: bool}
 */
function buildFeedSnapshot(array $feedDefinition): array
{
    $feedDocument = loadFeed($feedDefinition['url']);
    if (!$feedDocument instanceof SimpleXMLElement) {
        return [
            'label' => (string) $feedDefinition['label'],
            'url' => (string) $feedDefinition['url'],
            'accent' => (string) $feedDefinition['accent'],
            'items' => [],
            'ok' => false,
        ];
    }

    $feedItems = [];
    foreach ($feedDocument->channel->item as $item) {
        $feedItems[] = buildItemViewModel($item, (string) $feedDefinition['label']);
    }

    return [
        'label' => (string) $feedDefinition['label'],
        'url' => (string) $feedDefinition['url'],
        'accent' => (string) $feedDefinition['accent'],
        'items' => array_slice($feedItems, 0, 3),
        'ok' => true,
    ];
}

$snapshots = array_map('buildFeedSnapshot', ESPN_FEEDS);
$combinedItems = [];
foreach ($snapshots as $snapshot) {
    foreach ($snapshot['items'] as $item) {
        $combinedItems[] = $item;
    }
}
$featuredItem = $combinedItems[0] ?? [
    'feed' => 'ESPN',
    'title' => 'No headlines available right now',
    'summary' => 'The feed could not be loaded or returned no items.',
    'published' => '',
    'link' => 'https://www.espn.com/',
    'image' => '',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESPN Multi View</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <main class="page-shell">
        <section class="hero">
            <p class="eyebrow">Source-Specific Sample</p>
            <h1>ESPN multi-view feed dashboard</h1>
            <p class="lead">
                This page combines several ESPN RSS feeds into a richer display
                with a featured story, image cards, and a compact headline view.
            </p>
            <div class="hero-meta">
                <span class="chip">Server-rendered RSS</span>
                <span class="chip">Multiple ESPN feeds</span>
                <span class="chip">Image-aware cards</span>
            </div>
        </section>

        <section class="featured panel">
            <div class="featured-copy">
                <p class="card-label">Featured</p>
                <h2><?= escapeHtml($featuredItem['title']) ?></h2>
                <p class="muted">
                    <?= escapeHtml($featuredItem['feed']) ?>
                    &middot;
                    <?= escapeHtml(formatTimestamp($featuredItem['published'])) ?>
                </p>
                <p class="feature-summary">
                    <?= escapeHtml($featuredItem['summary']) ?>
                </p>
                <a class="button" href="<?= escapeHtml($featuredItem['link']) ?>" target="_blank" rel="noopener noreferrer">
                    Read article
                </a>
            </div>

            <div class="featured-media">
                <?php if ($featuredItem['image'] !== ''): ?>
                    <img src="<?= escapeHtml($featuredItem['image']) ?>" alt="<?= escapeHtml($featuredItem['title']) ?>">
                <?php else: ?>
                    <div class="media-placeholder">
                        <span>ESPN</span>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="feed-grid">
            <?php foreach ($snapshots as $snapshot): ?>
                <article class="feed-card <?= escapeHtml($snapshot['accent']) ?>">
                    <div class="feed-header">
                        <div>
                            <p class="card-label"><?= escapeHtml($snapshot['label']) ?></p>
                            <h3><?= escapeHtml($snapshot['label']) ?></h3>
                        </div>
                        <span class="pill"><?= count($snapshot['items']) ?> stories</span>
                    </div>

                    <?php if ($snapshot['items'] === []): ?>
                        <p class="muted">No stories loaded for this feed.</p>
                    <?php else: ?>
                        <div class="story-list">
                            <?php foreach ($snapshot['items'] as $item): ?>
                                <article class="story-item">
                                    <?php if ($item['image'] !== ''): ?>
                                        <img class="story-image" src="<?= escapeHtml($item['image']) ?>" alt="<?= escapeHtml($item['title']) ?>">
                                    <?php else: ?>
                                        <div class="story-image story-image-fallback">
                                            <span><?= escapeHtml(substr($snapshot['label'], 0, 2)) ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="story-copy">
                                        <a class="story-title" href="<?= escapeHtml($item['link']) ?>" target="_blank" rel="noopener noreferrer">
                                            <?= escapeHtml($item['title']) ?>
                                        </a>
                                        <p class="story-meta">
                                            <?= escapeHtml(formatTimestamp($item['published'])) ?>
                                        </p>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="panel">
            <div class="panel-header">
                <div>
                    <p class="card-label">Compact view</p>
                    <h2>Latest combined headlines</h2>
                </div>
                <p class="muted">A quick scan view for smaller screens.</p>
            </div>

            <div class="headline-table">
                <?php foreach (array_slice($combinedItems, 0, 12) as $item): ?>
                    <article class="headline-row">
                        <div>
                            <p class="headline-feed"><?= escapeHtml($item['feed']) ?></p>
                            <a href="<?= escapeHtml($item['link']) ?>" target="_blank" rel="noopener noreferrer">
                                <?= escapeHtml($item['title']) ?>
                            </a>
                        </div>
                        <span class="headline-date"><?= escapeHtml(formatTimestamp($item['published'])) ?></span>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
</body>
</html>
