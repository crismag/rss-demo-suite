<?php
declare(strict_types=1);

/**
 * PHP web dashboard for the Level 2 RSS demo.
 *
 * What this page does:
 *  - reads the SQLite database created by the ingest tutorial
 *  - shows the latest recorded ingest run
 *  - shows the newest stored RSS entries
 *  - optionally refreshes the database by running the ingest script
 *
 * Inputs:
 *  - GET parameter refresh=1 to force a refresh
 *  - the SQLite database at level2-aggregator/db.sqlite
 *
 * Outputs:
 *  - a small HTML dashboard with ingest status and recent entries
 */

require_once __DIR__ . '/../../shared/php/db.php';

const DASHBOARD_DATABASE_PATH = 'level2-aggregator/db.sqlite';
const DASHBOARD_INGEST_SCRIPT = __DIR__ . '/../../level2-aggregator/php/ingest.php';
const DASHBOARD_ENTRY_LIMIT = 8;
const DASHBOARD_STALE_AFTER_SECONDS = 3600;

/**
 * Format an ISO-8601 timestamp for display.
 */
function formatTimestamp(string $timestamp): string
{
    if ($timestamp === '') {
        return 'Not recorded yet';
    }

    try {
        $dateTime = new DateTimeImmutable($timestamp);
        return $dateTime->format('Y-m-d H:i:s T');
    } catch (Throwable $exception) {
        return $timestamp;
    }
}

/**
 * Return the number of seconds since a timestamp, or null when unavailable.
 */
function secondsSince(string $timestamp): ?int
{
    if ($timestamp === '') {
        return null;
    }

    try {
        $dateTime = new DateTimeImmutable($timestamp);
        return time() - $dateTime->getTimestamp();
    } catch (Throwable $exception) {
        return null;
    }
}

/**
 * Decide whether the dashboard should refresh the feed data automatically.
 */
function shouldRefreshAutomatically(array $latestRun): bool
{
    if ($latestRun === []) {
        return true;
    }

    $status = strtolower((string) ($latestRun['status'] ?? ''));
    if ($status === 'failed') {
        return false;
    }

    $finishedAt = (string) ($latestRun['finished_at'] ?? '');
    $ageInSeconds = secondsSince($finishedAt);
    if ($ageInSeconds === null) {
        return true;
    }

    return $ageInSeconds >= DASHBOARD_STALE_AFTER_SECONDS;
}

/**
 * Run the ingest script through the PHP command line when the server allows it.
 *
 * @return array{success: bool, output: string}
 */
function refreshData(string $scriptPath, string $databasePath): array
{
    if (!function_exists('shell_exec')) {
        return [
            'success' => false,
            'output' => 'shell_exec is disabled on this server.',
        ];
    }

    $phpBinary = defined('PHP_BINARY') && PHP_BINARY !== '' ? PHP_BINARY : 'php';
    $command = escapeshellarg($phpBinary)
        . ' '
        . escapeshellarg($scriptPath)
        . ' --db '
        . escapeshellarg($databasePath)
        . ' 2>&1';

    if (!function_exists('proc_open')) {
        if (function_exists('exec')) {
            $commandLines = [];
            $exitCode = 0;
            exec($command, $commandLines, $exitCode);
            return [
                'success' => $exitCode === 0,
                'output' => trim(implode(PHP_EOL, $commandLines)),
            ];
        }

        $output = shell_exec($command);
        return [
            'success' => $output !== null,
            'output' => trim((string) $output),
        ];
    }

    $descriptorSpec = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];
    $process = proc_open($command, $descriptorSpec, $pipes);
    if (!is_resource($process)) {
        return [
            'success' => false,
            'output' => 'Unable to launch the ingest script.',
        ];
    }

    fclose($pipes[0]);
    $standardOutput = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    $standardError = stream_get_contents($pipes[2]);
    fclose($pipes[2]);
    $exitCode = proc_close($process);

    return [
        'success' => $exitCode === 0,
        'output' => trim($standardOutput . PHP_EOL . $standardError),
    ];
}

/**
 * Render a readable age string for the ingest summary card.
 */
function formatAgeLabel(?int $ageInSeconds): string
{
    if ($ageInSeconds === null) {
        return 'Unknown age';
    }

    if ($ageInSeconds < 60) {
        return $ageInSeconds . ' seconds ago';
    }

    $minutes = intdiv($ageInSeconds, 60);
    if ($minutes < 60) {
        return $minutes . ' minutes ago';
    }

    $hours = intdiv($minutes, 60);
    return $hours . ' hours ago';
}

/**
 * Escape text for safe HTML output.
 */
function escapeHtml(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Read the current ingest state before doing anything else.
$latestRun = fetchLatestIngestRun(DASHBOARD_DATABASE_PATH);
$refreshMessage = '';
$noticeTone = 'neutral';

// Allow a manual refresh and also refresh stale data automatically.
$refreshRequested = isset($_GET['refresh']) && $_GET['refresh'] === '1';
if ($refreshRequested || shouldRefreshAutomatically($latestRun)) {
    $refreshResult = refreshData(DASHBOARD_INGEST_SCRIPT, DASHBOARD_DATABASE_PATH);
    if ($refreshResult['success']) {
        $refreshMessage = 'Feed data refreshed from the ingest script.';
        $noticeTone = 'good';
        $latestRun = fetchLatestIngestRun(DASHBOARD_DATABASE_PATH);
    } else {
        $refreshMessage = $refreshResult['output'];
        $noticeTone = 'danger';
    }
}

// Load the newest entries after the refresh attempt so the page stays current.
$recentEntries = fetchEntries(DASHBOARD_DATABASE_PATH, DASHBOARD_ENTRY_LIMIT);
$latestRunAge = secondsSince((string) ($latestRun['finished_at'] ?? ''));
$latestRunStatus = strtolower((string) ($latestRun['status'] ?? ''));
$statusLabel = $latestRunStatus === '' ? 'Not recorded yet' : strtoupper($latestRunStatus);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RSS Demo Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <main class="shell">
        <section class="hero">
            <div>
                <p class="eyebrow">Level 2 PHP Web Dashboard</p>
                <h1>RSS ingest status and newest entries</h1>
                <p class="lead">
                    This page reads the shared SQLite database, shows the latest
                    ingest run, and previews the newest stored RSS items.
                </p>
            </div>
            <div class="actions">
                <a class="button" href="?refresh=1">Refresh Data</a>
                <p class="hint">
                    Database: <code>level2-aggregator/db.sqlite</code>
                </p>
            </div>
        </section>

        <?php if ($refreshMessage !== ''): ?>
            <section class="notice <?= escapeHtml($noticeTone) ?>">
                <?= escapeHtml($refreshMessage) ?>
            </section>
        <?php endif; ?>

        <section class="cards">
            <article class="card">
                <p class="card-label">Latest Ingest</p>
                <h2><?= escapeHtml($statusLabel) ?></h2>
                <p><?= escapeHtml(formatTimestamp((string) ($latestRun['finished_at'] ?? ''))) ?></p>
                <p class="muted">
                    <?= escapeHtml(formatAgeLabel($latestRunAge)) ?>
                </p>
            </article>

            <article class="card">
                <p class="card-label">Feeds Loaded</p>
                <h2><?= escapeHtml((string) ($latestRun['feed_count'] ?? '0')) ?></h2>
                <p class="muted">Configured feed sources in the last run.</p>
            </article>

            <article class="card">
                <p class="card-label">Entries Seen</p>
                <h2><?= escapeHtml((string) ($latestRun['entry_count'] ?? '0')) ?></h2>
                <p class="muted">Entries parsed before saving to SQLite.</p>
            </article>

            <article class="card">
                <p class="card-label">New Inserts</p>
                <h2><?= escapeHtml((string) ($latestRun['inserted_count'] ?? '0')) ?></h2>
                <p class="muted">Rows added during the most recent ingest.</p>
            </article>
        </section>

        <section class="panel">
            <div class="panel-header">
                <div>
                    <p class="card-label">Recent Items</p>
                    <h2>Newest stored entries</h2>
                </div>
                <p class="muted">
                    Showing the latest <?= escapeHtml((string) DASHBOARD_ENTRY_LIMIT) ?> items.
                </p>
            </div>

            <?php if ($recentEntries === []): ?>
                <p class="empty-state">
                    No stored entries yet. Run the ingest demo first, or press
                    <a href="?refresh=1">Refresh Data</a>.
                </p>
            <?php else: ?>
                <div class="entries">
                    <?php foreach ($recentEntries as $entry): ?>
                        <article class="entry">
                            <div class="entry-top">
                                <h3>
                                    <a href="<?= escapeHtml((string) ($entry['link'] ?? '#')) ?>" target="_blank" rel="noreferrer">
                                        <?= escapeHtml((string) ($entry['title'] ?? '(no title)')) ?>
                                    </a>
                                </h3>
                                <span class="pill"><?= escapeHtml((string) ($entry['category'] ?? 'uncategorized')) ?></span>
                            </div>
                            <p class="entry-meta">
                                <?= escapeHtml((string) ($entry['feed_name'] ?? 'Unknown feed')) ?>
                                •
                                <?= escapeHtml(formatTimestamp((string) ($entry['published'] ?? ''))) ?>
                            </p>
                            <p class="entry-summary">
                                <?= escapeHtml((string) ($entry['summary'] ?? '')) ?>
                            </p>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
