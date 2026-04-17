"""Export RSS entries to JSON for the table web sample.

What this script does:
    - reads entries from the SQLite database
    - writes them to ``web/table/data/entries.json``

Usage:
    python scripts/python/export_table_json.py
    python scripts/python/export_table_json.py --db level2-aggregator/db.sqlite
    python scripts/python/export_table_json.py --out web/table/data/entries.json

Inputs:
    - ``--db``: path to the SQLite database file
    - ``--out``: path to the JSON output file
    - ``--limit``: maximum number of entries to export

Outputs:
    - a JSON file containing the exported RSS entries
    - console text showing where the file was written
"""

from __future__ import annotations

import argparse
import json
import sys
from pathlib import Path

repo_root = Path(__file__).resolve().parents[2]
sys.path.insert(0, str(repo_root))

from shared.python.db import fetch_entries


DEFAULT_DATABASE_PATH = "level2-aggregator/db.sqlite"
DEFAULT_OUTPUT_PATH = "web/table/data/entries.json"
DEFAULT_EXPORT_LIMIT = 200


def build_parser() -> argparse.ArgumentParser:
    """Create the command-line argument parser for the export script."""
    parser = argparse.ArgumentParser(
        description="Export SQLite entries to JSON for the table sample",
    )
    parser.add_argument(
        "--db",
        default=DEFAULT_DATABASE_PATH,
        help="Path to SQLite database file",
    )
    parser.add_argument(
        "--out",
        default=DEFAULT_OUTPUT_PATH,
        help="Output JSON path",
    )
    parser.add_argument(
        "--limit",
        type=int,
        default=DEFAULT_EXPORT_LIMIT,
        help="Maximum number of entries",
    )
    return parser


def main() -> None:
    """Run the JSON export tutorial and write the output file."""
    parser = build_parser()
    arguments = parser.parse_args()

    # Make sure the destination folder exists before writing the export file.
    output_path = Path(arguments.out)
    output_path.parent.mkdir(parents=True, exist_ok=True)

    entries = fetch_entries(arguments.db, limit=arguments.limit)
    output_path.write_text(json.dumps(entries, indent=2), encoding="utf-8")
    print(f"Exported {len(entries)} entries to {output_path}")


if __name__ == "__main__":
    main()
