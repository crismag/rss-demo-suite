# rss-demo-suite

RSS Demo Suite: from simple feed parsing to a queryable data platform.

## Structure

- `level1-basic/`: simple RSS parsing demo
- `level2-aggregator/`: reserved for ingestion pipeline demos
- `level3-insights/`: reserved for analytics demos
- `shared/`: reusable modules (config, db, parsing, query engine)
- `scripts/`: ingestion and JSON export scripts
- `web/`: static JSON output target for UI usage

## Setup

```bash
python -m pip install -r requirements.txt
```

## Run demos

### Level 1: parse one feed per category

```bash
python level1-basic/demo_parse.py
```

### Ingest configured feeds to SQLite

```bash
python scripts/ingest.py
```

### Export entries to JSON for static UI

```bash
python scripts/export_json.py
```

## Configuration

Feed sources are configured in `feeds.yaml` by category.
