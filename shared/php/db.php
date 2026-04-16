<?php
declare(strict_types=1);

/**
 * SQLite helpers for the RSS Demo Suite PHP tutorials.
 *
 * What this module does:
 *  - creates the SQLite table used by the Level 2 demo
 *  - inserts normalized feed entries while ignoring duplicates
 *  - reads back stored entries for the query demo
 *
 * Inputs:
 *  - SQLite database path
 *  - normalized entry arrays
 *
 * Outputs:
 *  - stored rows in SQLite
 *  - arrays of rows when querying data back out
 */

/**
 * Resolve a database path relative to the repository root.
 */
function resolveDatabasePath(string $dbPath): string
{
    if (
        str_starts_with($dbPath, DIRECTORY_SEPARATOR)
        || preg_match('/^[A-Za-z]:[\\\\\\/]/', $dbPath) === 1
    ) {
        return $dbPath;
    }

    $repoRoot = dirname(__DIR__, 2);
    return $repoRoot . DIRECTORY_SEPARATOR . $dbPath;
}

/**
 * Create the SQLite schema if it does not already exist.
 */
function initDb(string $dbPath): void
{
    $resolvedDbPath = resolveDatabasePath($dbPath);
    $databaseDirectory = dirname($resolvedDbPath);
    if (!is_dir($databaseDirectory)) {
        mkdir($databaseDirectory, 0777, true);
    }

    $database = new SQLite3($resolvedDbPath);
    $database->exec(
        'CREATE TABLE IF NOT EXISTS entries (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            entry_key TEXT NOT NULL UNIQUE,
            feed_name TEXT NOT NULL,
            feed_url TEXT NOT NULL,
            category TEXT NOT NULL,
            title TEXT NOT NULL,
            link TEXT NOT NULL,
            summary TEXT NOT NULL,
            published TEXT NOT NULL
        )'
    );
    $database->exec(
        'CREATE INDEX IF NOT EXISTS idx_entries_category
         ON entries(category)'
    );
    $database->exec(
        'CREATE INDEX IF NOT EXISTS idx_entries_published
         ON entries(published)'
    );
    $database->exec(
        'CREATE TABLE IF NOT EXISTS ingest_runs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            started_at TEXT NOT NULL,
            finished_at TEXT NOT NULL,
            status TEXT NOT NULL,
            feed_count INTEGER NOT NULL,
            entry_count INTEGER NOT NULL,
            inserted_count INTEGER NOT NULL,
            message TEXT NOT NULL
        )'
    );
    $database->exec(
        'CREATE INDEX IF NOT EXISTS idx_ingest_runs_finished_at
         ON ingest_runs(finished_at)'
    );
    $database->close();
}

/**
 * Build a stable unique key for a feed entry.
 */
function buildEntryKey(array $entry): string
{
    return hash(
        'sha256',
        implode(
            '|',
            [
                trim((string) ($entry['feed_url'] ?? '')),
                trim((string) ($entry['link'] ?? '')),
                trim((string) ($entry['title'] ?? '')),
                trim((string) ($entry['published'] ?? '')),
            ]
        )
    );
}

/**
 * Insert parsed entries into SQLite while ignoring duplicates.
 *
 * @param array<int, array<string, string>> $entries
 */
function upsertEntries(string $dbPath, array $entries): int
{
    initDb($dbPath);

    $database = new SQLite3(resolveDatabasePath($dbPath));
    $statement = $database->prepare(
        'INSERT OR IGNORE INTO entries (
            entry_key, feed_name, feed_url, category,
            title, link, summary, published
        ) VALUES (
            :entry_key, :feed_name, :feed_url, :category,
            :title, :link, :summary, :published
        )'
    );

    $insertedCount = 0;
    foreach ($entries as $entry) {
        $statement->bindValue(
            ':entry_key',
            buildEntryKey($entry),
            SQLITE3_TEXT
        );
        $statement->bindValue(
            ':feed_name',
            (string) ($entry['feed_name'] ?? ''),
            SQLITE3_TEXT
        );
        $statement->bindValue(
            ':feed_url',
            (string) ($entry['feed_url'] ?? ''),
            SQLITE3_TEXT
        );
        $statement->bindValue(
            ':category',
            (string) ($entry['category'] ?? ''),
            SQLITE3_TEXT
        );
        $statement->bindValue(
            ':title',
            (string) ($entry['title'] ?? ''),
            SQLITE3_TEXT
        );
        $statement->bindValue(
            ':link',
            (string) ($entry['link'] ?? ''),
            SQLITE3_TEXT
        );
        $statement->bindValue(
            ':summary',
            (string) ($entry['summary'] ?? ''),
            SQLITE3_TEXT
        );
        $statement->bindValue(
            ':published',
            (string) ($entry['published'] ?? ''),
            SQLITE3_TEXT
        );

        $statement->execute();
        if ($database->changes() > 0) {
            $insertedCount++;
        }
    }

    $database->close();
    return $insertedCount;
}

/**
 * Read the newest entries from SQLite.
 *
 * @return array<int, array<string, string>>
 */
function fetchEntries(string $dbPath, int $limit = 200): array
{
    initDb($dbPath);

    if ($limit <= 0) {
        return [];
    }

    $database = new SQLite3(resolveDatabasePath($dbPath));
    $statement = $database->prepare(
        'SELECT feed_name, feed_url, category, title, link, summary, published
         FROM entries
         ORDER BY published DESC, id DESC
         LIMIT :limit'
    );
    $statement->bindValue(':limit', $limit, SQLITE3_INTEGER);

    $result = $statement->execute();
    if ($result === false) {
        $database->close();
        return [];
    }

    $entries = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $entries[] = [
            'feed_name' => (string) ($row['feed_name'] ?? ''),
            'feed_url' => (string) ($row['feed_url'] ?? ''),
            'category' => (string) ($row['category'] ?? ''),
            'title' => (string) ($row['title'] ?? ''),
            'link' => (string) ($row['link'] ?? ''),
            'summary' => (string) ($row['summary'] ?? ''),
            'published' => (string) ($row['published'] ?? ''),
        ];
    }

    $database->close();
    return $entries;
}

/**
 * Store a record describing one ingestion run.
 *
 * @param array{
 *     started_at: string,
 *     finished_at: string,
 *     status: string,
 *     feed_count: int,
 *     entry_count: int,
 *     inserted_count: int,
 *     message: string
 * } $run
 */
function recordIngestRun(string $dbPath, array $run): void
{
    initDb($dbPath);

    $database = new SQLite3(resolveDatabasePath($dbPath));
    $statement = $database->prepare(
        'INSERT INTO ingest_runs (
            started_at, finished_at, status,
            feed_count, entry_count, inserted_count, message
        ) VALUES (
            :started_at, :finished_at, :status,
            :feed_count, :entry_count, :inserted_count, :message
        )'
    );
    $statement->bindValue(
        ':started_at',
        (string) ($run['started_at'] ?? ''),
        SQLITE3_TEXT
    );
    $statement->bindValue(
        ':finished_at',
        (string) ($run['finished_at'] ?? ''),
        SQLITE3_TEXT
    );
    $statement->bindValue(
        ':status',
        (string) ($run['status'] ?? 'success'),
        SQLITE3_TEXT
    );
    $statement->bindValue(
        ':feed_count',
        (int) ($run['feed_count'] ?? 0),
        SQLITE3_INTEGER
    );
    $statement->bindValue(
        ':entry_count',
        (int) ($run['entry_count'] ?? 0),
        SQLITE3_INTEGER
    );
    $statement->bindValue(
        ':inserted_count',
        (int) ($run['inserted_count'] ?? 0),
        SQLITE3_INTEGER
    );
    $statement->bindValue(
        ':message',
        (string) ($run['message'] ?? ''),
        SQLITE3_TEXT
    );

    $statement->execute();
    $database->close();
}

/**
 * Read the most recent ingestion run from SQLite.
 *
 * @return array<string, string>
 */
function fetchLatestIngestRun(string $dbPath): array
{
    initDb($dbPath);

    $database = new SQLite3(resolveDatabasePath($dbPath));
    $result = $database->query(
        'SELECT started_at, finished_at, status,
                feed_count, entry_count, inserted_count, message
         FROM ingest_runs
         ORDER BY finished_at DESC, id DESC
         LIMIT 1'
    );

    if ($result === false) {
        $database->close();
        return [];
    }

    $row = $result->fetchArray(SQLITE3_ASSOC);
    $database->close();

    if ($row === false) {
        return [];
    }

    return [
        'started_at' => (string) ($row['started_at'] ?? ''),
        'finished_at' => (string) ($row['finished_at'] ?? ''),
        'status' => (string) ($row['status'] ?? ''),
        'feed_count' => (string) ($row['feed_count'] ?? '0'),
        'entry_count' => (string) ($row['entry_count'] ?? '0'),
        'inserted_count' => (string) ($row['inserted_count'] ?? '0'),
        'message' => (string) ($row['message'] ?? ''),
    ];
}
