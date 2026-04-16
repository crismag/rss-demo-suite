# Python Scripts

This folder contains the shared tutorial scripts used by the web samples.

## Export Scripts

- `scripts/python/export_json.py` writes entries to `web/basic/data/entries.json`
- `scripts/python/export_table_json.py` writes entries to `web/table/data/entries.json`
- `scripts/python/rss_ingestion_engine.py` fills the SQLite database used by the
  higher-level examples

## What To Notice

- the basic and table pages can each have their own export target
- the table export keeps the table sample self-contained
- the shared ingestion engine still feeds the database-backed demos

