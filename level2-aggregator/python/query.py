"""Level 2 RSS query demo.

What this script does:
    - loads stored RSS entries from SQLite
    - filters them by category and keyword
    - prints a JSON preview of the results

Usage:
    python level2-aggregator/python/query.py --category tech --keyword AI

Inputs:
    - ``--db``: path to the SQLite database file
    - ``--keyword``: optional keyword filter for titles and summaries
    - ``--category``: optional category filter
    - ``--limit``: maximum number of matching entries to print

Outputs:
    - JSON text written to the terminal
"""

from __future__ import annotations

import argparse
import json
import sys
from datetime import datetime, timedelta
from pathlib import Path

repo_root = Path(__file__).resolve().parents[2]
sys.path.insert(0, str(repo_root))

from shared.python.db import fetch_entries
from shared.python.query_engine import filter_entries


DEFAULT_DATABASE_PATH = str(
    Path(__file__).resolve().parents[1] / "db.sqlite"
)
DEFAULT_ENTRY_LIMIT = 20
ENTRY_FETCH_LIMIT = 500


def build_parser() -> argparse.ArgumentParser:
    """Create the command-line argument parser for the query demo."""
    parser = argparse.ArgumentParser(description="Query RSS entries")
    parser.add_argument(
        "--db",
        default=DEFAULT_DATABASE_PATH,
        help="Path to SQLite database file",
    )
    parser.add_argument(
        "--keyword",
        default=None,
        help="Keyword in title/summary",
    )
    parser.add_argument(
        "--category",
        default=None,
        help="Category filter",
    )
    parser.add_argument(
        "--limit",
        type=int,
        default=DEFAULT_ENTRY_LIMIT,
        help="Maximum entries returned",
    )
    parser.add_argument(
        "--sort",
        choices=["latest", "oldest"],
        default="latest",
        help="Sort order",
    )
    parser.add_argument(
        "--days",
        type=int,
        default=None,
        help="Filter entries from last N days",
    )
    parser.add_argument(
        "--format",
        choices=["json", "text"],
        default="json",
    )
    return parser


def main() -> None:
    """Run the query tutorial and print matching entries as JSON."""
    parser = build_parser()
    arguments = parser.parse_args()

    print(f"[INFO] Loading entries from {arguments.db}")

    # Load a slightly larger pool first so the filters have enough data to work
    # with.
    entries = fetch_entries(arguments.db, limit=ENTRY_FETCH_LIMIT)

    # Apply the requested filters and trim the final output list.
    filtered_entries = filter_entries(
        entries,
        keyword=arguments.keyword,
        category=arguments.category,
        limit=arguments.limit,
    )

    # Optional: restrict to last N days
    if arguments.days:
        cutoff = datetime.utcnow() - timedelta(days=arguments.days)

        def within_days(entry: dict) -> bool:
            try:
                dt = datetime.fromisoformat(entry.get("published", ""))
                return dt >= cutoff
            except Exception:
                return False

        filtered_entries = list(filter(within_days, filtered_entries))

    print(f"[INFO] Found {len(filtered_entries)} matching entries")

    # Sorting
    if arguments.sort == "latest":
        filtered_entries = sorted(
            filtered_entries,
            key=lambda x: x.get("published", ""),
            reverse=True,
        )
    else:
        filtered_entries = sorted(
            filtered_entries,
            key=lambda x: x.get("published", ""),
        )

    # Output formatting
    if arguments.format == "json":
        print(json.dumps(filtered_entries, indent=2))
    else:
        for entry in filtered_entries:
            title = entry.get("title") or "(no title)"
            pub = entry.get("published") or ""
            print(f"- {title} {pub}")


if __name__ == "__main__":
    main()
