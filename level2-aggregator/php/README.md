# Level 2: Aggregator in PHP

This tutorial level is the PHP version of the Level 2 RSS demos. It shows the
two main steps of the aggregation workflow:

1. ingest feeds into SQLite
2. query the stored entries back out as JSON

The database path is resolved from the repository root, so you can run these
scripts from the `level2-aggregator/php/` folder without changing the code.

## Run It

Ingest the sample feeds:

```bash
php level2-aggregator/php/ingest.php
```

Query the stored entries:

```bash
php level2-aggregator/php/query.php --category tech --limit 5
```

## Inputs

- `feeds.yaml` provides the sample feed list.
- `--config` lets you point the ingest script at a different YAML file.
- `--db` controls the SQLite database location for both scripts.
- `--keyword`, `--category`, and `--limit` control how query results are
  filtered.

## Important Code Sections

- `buildArguments()` in each script reads the command-line options and applies
  defaults.
- `loadFeedsConfig()` in `shared/php/utils.php` reads the feed list from YAML.
- `normalizeEntry()` in `shared/php/utils.php` converts RSS items into a
  database-friendly array.
- `initDb()`, `upsertEntries()`, and `fetchEntries()` in `shared/php/db.php`
  handle the SQLite table and data access.
- `recordIngestRun()` and `fetchLatestIngestRun()` in `shared/php/db.php`
  store and read the most recent ingest summary for the web dashboard.
- `filterEntries()` in `shared/php/query_engine.php` applies category,
  keyword, and limit filters.
- `main()` in each script coordinates the workflow and prints the result.

## What To Observe

- The ingest script prints how many entries were inserted.
- The query script prints JSON to the terminal.
- Both scripts use the same SQLite database path by default.
- The query script fetches a larger result set first, then applies filters.
- The database file is created at `level2-aggregator/db.sqlite` in the repo
  root.

## What Happens

### Ingest Script

1. The script loads the feed list from `feeds.yaml`.
2. It downloads each RSS feed.
3. It normalizes each item into a consistent array shape.
4. It inserts the entries into SQLite.
5. It prints the number of newly inserted rows.

### Query Script

1. The script opens the SQLite database.
2. It reads the newest stored entries.
3. It filters them by category and keyword if requested.
4. It trims the list to the requested limit.
5. It prints the result as pretty JSON.

## What Else Can Be Done

- Change the feed list in `feeds.yaml` and rerun ingestion.
- Try different `--category` and `--keyword` combinations.
- Increase `--limit` to inspect more results.
- Add another PHP script later that summarizes or counts entries.
- Open `web/php-dashboard/` after ingesting to see the latest run and newest
  entries in a browser.

## Output

- `level2-aggregator/db.sqlite` after ingestion.
- JSON text printed by the query script.
