# Level 2: Aggregator

This level shows how the tutorial turns RSS feeds into stored entries and
simple query results.

## Ingest

Run ingestion from the repository root so shared imports resolve:

```bash
python level2-aggregator/python/ingest_feeds.py
# Or run the shared engine directly:
python scripts/python/rss_ingestion_engine.py
```

## Query

Run queries from the repository root. Examples:

```bash
# Text output (demo mode)
python level2-aggregator/python/query.py --category tech --limit 5 --format text

# JSON output (dev mode), last 7 days, sorted newest-first
python level2-aggregator/python/query.py --limit 10 --sort latest --days 7 --format json
```

Notes:
- Always run from the repository root (the folder containing `feeds.yaml` and `shared/`).
- The query script supports: `--keyword`, `--category`, `--limit`, `--sort` (latest|oldest), `--days`, and `--format` (json|text).

## Inputs

- `feeds.yaml` for the ingest script.
- `--db` for the SQLite database path.
- `--keyword`, `--category`, and `--limit` for the query script.

## Outputs

- `level2-aggregator/db.sqlite` after ingestion.
- JSON text printed by the query script.
