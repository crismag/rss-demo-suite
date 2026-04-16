"""Level 2 demo: query ingested entries with simple filters."""

from __future__ import annotations

import argparse
import json
import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parents[1]))

from shared.db import fetch_entries
from shared.query_engine import filter_entries


def main() -> None:
    """Run a basic query against stored entries."""
    parser = argparse.ArgumentParser(description="Query RSS entries")
    parser.add_argument("--db", default="level2-aggregator/db.sqlite", help="Path to SQLite database file")
    parser.add_argument("--keyword", default=None, help="Keyword in title/summary")
    parser.add_argument("--category", default=None, help="Category filter")
    parser.add_argument("--limit", type=int, default=20, help="Maximum entries returned")
    args = parser.parse_args()

    entries = fetch_entries(args.db, limit=500)
    filtered = filter_entries(entries, keyword=args.keyword, category=args.category, limit=args.limit)
    print(json.dumps(filtered, indent=2))


if __name__ == "__main__":
    main()
