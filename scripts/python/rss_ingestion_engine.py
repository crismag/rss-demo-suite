"""Shared RSS ingestion script.

What this script does:
    - loads the feed configuration from ``feeds.yaml``
    - downloads each RSS feed
    - normalizes feed entries
    - stores them in SQLite

Usage:
    python scripts/python/rss_ingestion_engine.py
    python scripts/python/rss_ingestion_engine.py --config feeds.yaml
    python scripts/python/rss_ingestion_engine.py --db level2-aggregator/db.sqlite

Inputs:
    - ``--config``: path to the YAML feed configuration file
    - ``--db``: path to the SQLite database file

Outputs:
    - console text showing how many new entries were inserted
"""

from __future__ import annotations

import argparse
import sys
from pathlib import Path

import feedparser

repo_root = Path(__file__).resolve().parents[2]
sys.path.insert(0, str(repo_root))

from shared.python.db import init_db, upsert_entries
from shared.python.utils import load_feeds_config, normalize_entry


DEFAULT_CONFIG_PATH = "feeds.yaml"
DEFAULT_DATABASE_PATH = "level2-aggregator/db.sqlite"


def build_parser() -> argparse.ArgumentParser:
    """Create the command-line argument parser for the ingestion script."""
    parser = argparse.ArgumentParser(
        description="Ingest RSS feeds into SQLite",
    )
    parser.add_argument(
        "--config",
        default=DEFAULT_CONFIG_PATH,
        help="Path to feeds YAML config",
    )
    parser.add_argument(
        "--db",
        default=DEFAULT_DATABASE_PATH,
        help="Path to SQLite database file",
    )
    return parser


def ingest(config_path: str | Path, db_path: str | Path) -> int:
    """Fetch and parse configured feeds, then store entries in SQLite."""
    feed_definitions = load_feeds_config(config_path)
    init_db(db_path)

    parsed_entries: list[dict[str, str]] = []
    # Download each configured feed and normalize the entries for storage.
    for feed_definition in feed_definitions:
        parsed_feed = feedparser.parse(feed_definition["url"])
        for parsed_entry in parsed_feed.entries:
            parsed_entries.append(
                normalize_entry(feed_definition, parsed_entry)
            )

    return upsert_entries(db_path, parsed_entries)


def main() -> None:
    """Run the shared feed ingestion workflow."""
    parser = build_parser()
    arguments = parser.parse_args()

    # Reuse the ingestion helper so the CLI stays small and easy to follow.
    inserted = ingest(arguments.config, arguments.db)
    print(f"Inserted {inserted} new entries.")


if __name__ == "__main__":
    main()
