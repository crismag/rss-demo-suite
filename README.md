# RSS Demo Suite

RSS Demo Suite is a tutorial project that shows how RSS feeds can move from a
simple parser to a searchable, exportable demo pipeline.

## Quick Start

```bash
python -m pip install feedparser pyyaml
# Run the Level 2 tutorial wrapper or the shared engine
python level2-aggregator/python/ingest_feeds.py
# or
python scripts/python/rss_ingestion_engine.py
# Export JSON for the web demo
python scripts/python/export_json.py
```

## What The Demo Covers

### Level 1: Basic Parsing

The first tutorial script loads `feeds.yaml`, picks one feed, and prints a few
entry titles.

Run it with:

```bash
python level1-basic/python/demo.py
php level1-basic/php/demo.php
```

The Python and PHP versions follow the same tutorial flow so you can compare
the two languages side by side.

### Level 2: Aggregation

The ingestion script downloads every configured feed and stores normalized
entries in SQLite.

Run it with:

```bash
python level2-aggregator/python/ingest_feeds.py
php level2-aggregator/php/ingest.php
```

The Python wrapper mirrors the shared engine so you can compare the same
workflow in two languages.

### Level 3: Query And Insights

The query and trends scripts show how to filter stored entries and summarize
their titles.

Run them with (run these commands from the repository root so imports resolve):

```bash
# Query the SQLite store (examples)
python level2-aggregator/python/query.py --category tech --keyword AI --limit 5 --format text
python level2-aggregator/python/query.py --limit 10 --sort latest --days 7 --format json

# Compute simple title trends
python level3-insights/python/trends.py --top 10
```

Notes:
- The scripts assume you run from the repository root (the folder that contains `feeds.yaml` and `shared/`).
- For the shared scripts under `scripts/python/` you can also run them as a module from the repo root: `python -m scripts.python.rss_ingestion_engine`.
- The `level2-aggregator` folder name contains a hyphen, so it is not importable as a module name with `-m` (use the file path form above when running the query script).

## Web Demo

Open `web/index.html` for the sample gallery.

The basic static web demo reads exported JSON from
`web/basic/data/entries.json`.

The PHP dashboard reads the SQLite database and shows the latest ingest
summary and newest entries from `web/php-dashboard/`.

Generate the file with:

```bash
python scripts/python/export_json.py
```

Other sample pages can live under `web/<sample-name>/` and follow the same
structure.

## Repository Layout

```text
rss-demo-suite/
|-- level1-basic/
|   |-- php/            # Basic RSS parsing tutorial in PHP
|   `-- python/         # Basic RSS parsing tutorial
|-- level2-aggregator/
|   |-- php/            # Aggregation and query tutorial in PHP
|   `-- python/         # Aggregation and query tutorial
|-- level3-insights/
|   `-- python/         # Trend and insights tutorial
|-- shared/
|   |-- php/            # Shared PHP helper modules
|   `-- python/         # Shared helper modules
|-- scripts/
|   `-- python/         # Ingestion and export scripts
|-- web/
|   |-- index.html      # Gallery landing page for the web samples
|   |-- basic/          # Current sample static web page
|   `-- php-dashboard/  # PHP dashboard for the shared SQLite data
|-- docs/               # Supporting documentation
`-- feeds.yaml          # Feed configuration
```

## Notes

- `feeds.yaml` defines the sample RSS feeds used by the demos.
- `level2-aggregator/db.sqlite` is created when you run ingestion.
- The repo is intentionally tutorial-oriented, so the scripts favor clarity
  over abstraction.
