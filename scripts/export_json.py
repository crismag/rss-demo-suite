"""Export RSS entries from SQLite to web/data JSON."""

from __future__ import annotations

import argparse
import json
import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parents[1]))

from shared.db import fetch_entries


def main() -> None:
    """CLI entrypoint for JSON export."""
    parser = argparse.ArgumentParser(description="Export SQLite entries to JSON")
    parser.add_argument("--db", default="level2-aggregator/db.sqlite", help="Path to SQLite database file")
    parser.add_argument("--out", default="web/data/entries.json", help="Output JSON path")
    parser.add_argument("--limit", type=int, default=200, help="Maximum number of entries")
    args = parser.parse_args()

    output_path = Path(args.out)
    output_path.parent.mkdir(parents=True, exist_ok=True)

    entries = fetch_entries(args.db, limit=args.limit)
    output_path.write_text(json.dumps(entries, indent=2), encoding="utf-8")
    print(f"Exported {len(entries)} entries to {output_path}")


if __name__ == "__main__":
    main()
