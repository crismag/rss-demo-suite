"""Ingestion script: parse RSS feeds and store entries in SQLite."""

from __future__ import annotations

import argparse
from pathlib import Path

from shared.config import load_feeds_config
from shared.db import get_connection, init_db, insert_entry, upsert_feed
from shared.rss import parse_feed


def ingest(config_path: Path, db_path: Path) -> None:
    """Ingest all configured feeds into the database."""
    feeds_by_category = load_feeds_config(config_path)
    init_db(db_path)

    inserted_total = 0
    with get_connection(db_path) as conn:
        for category, urls in feeds_by_category.items():
            for url in urls:
                parsed = parse_feed(url)
                feed_id = upsert_feed(conn, url=url, category=category, title=parsed.get("title"))

                inserted_count = 0
                for entry in parsed["entries"]:
                    if insert_entry(conn, feed_id, entry):
                        inserted_count += 1

                inserted_total += inserted_count
                print(f"{category}: {url} -> {inserted_count} new entries")

    print(f"Done. Inserted {inserted_total} entries.")


def parse_args() -> argparse.Namespace:
    """Parse CLI arguments."""
    root = Path(__file__).resolve().parents[1]
    parser = argparse.ArgumentParser(description="Ingest RSS feeds into SQLite database.")
    parser.add_argument(
        "--config",
        type=Path,
        default=root / "feeds.yaml",
        help="Path to feeds YAML configuration file.",
    )
    parser.add_argument(
        "--db",
        type=Path,
        default=root / "data" / "rss_demo.db",
        help="Path to SQLite database file.",
    )
    return parser.parse_args()


def main() -> None:
    """Run the ingestion script."""
    args = parse_args()
    args.db.parent.mkdir(parents=True, exist_ok=True)
    ingest(config_path=args.config, db_path=args.db)


if __name__ == "__main__":
    main()

