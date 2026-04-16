"""Ingest RSS feeds from feeds.yaml into SQLite."""

from __future__ import annotations

import argparse
import sys
from pathlib import Path

import feedparser

sys.path.insert(0, str(Path(__file__).resolve().parents[1]))

from shared.db import init_db, upsert_entries
from shared.utils import load_feeds_config, normalize_entry


def ingest(config_path: str, db_path: str) -> int:
    """Fetch and parse configured feeds, then store entries in SQLite."""
    feeds = load_feeds_config(config_path)
    init_db(db_path)

    parsed_entries: list[dict[str, str]] = []
    for feed in feeds:
        parsed = feedparser.parse(feed["url"])
        for entry in parsed.entries:
            parsed_entries.append(normalize_entry(feed, entry))

    return upsert_entries(db_path, parsed_entries)


def main() -> None:
    """CLI entrypoint for feed ingestion."""
    parser = argparse.ArgumentParser(description="Ingest RSS feeds into SQLite")
    parser.add_argument("--config", default="feeds.yaml", help="Path to feeds YAML config")
    parser.add_argument("--db", default="level2-aggregator/db.sqlite", help="Path to SQLite database file")
    args = parser.parse_args()

    inserted = ingest(args.config, args.db)
    print(f"Inserted {inserted} new entries.")


if __name__ == "__main__":
    main()
