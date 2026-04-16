# Demo Guide

This guide walks through the tutorial in the same order as the scripts.

## Steps

1. Load the sample feeds into SQLite with `python level2-aggregator/python/ingest_feeds.py`.
2. Export the stored entries to JSON with `python scripts/python/export_json.py`.
3. Preview filtered entries with `python level2-aggregator/python/query.py --category tech --limit 5 --format text`.
4. Open `web/basic/index.html` to view the basic sample page.

## Inputs

- `feeds.yaml` for feed configuration.
- `level2-aggregator/db.sqlite` as the generated data store.

## Outputs

- A populated SQLite database.
- `web/basic/data/entries.json` for the browser demo.
