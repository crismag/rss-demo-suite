"""Export queried entries from SQLite to JSON for static UI use."""

from __future__ import annotations

import argparse
import json
from pathlib import Path

from shared.query_engine import query_entries


def export_entries(
    db_path: Path,
    output_path: Path,
    category: str | None = None,
    keyword: str | None = None,
    limit: int = 100,
) -> None:
    """Export filtered entries from SQLite into a JSON file."""
    rows = query_entries(db_path=db_path, category=category, keyword=keyword, limit=limit)
    output_path.parent.mkdir(parents=True, exist_ok=True)
    output_path.write_text(json.dumps(rows, indent=2, ensure_ascii=False), encoding="utf-8")
    print(f"Wrote {len(rows)} rows to {output_path}")


def parse_args() -> argparse.Namespace:
    """Parse CLI arguments."""
    root = Path(__file__).resolve().parents[1]
    parser = argparse.ArgumentParser(description="Export entries from SQLite to JSON.")
    parser.add_argument(
        "--db",
        type=Path,
        default=root / "data" / "rss_demo.db",
        help="Path to SQLite database file.",
    )
    parser.add_argument(
        "--out",
        type=Path,
        default=root / "web" / "data" / "entries.json",
        help="Output JSON file path.",
    )
    parser.add_argument("--category", type=str, default=None, help="Category filter.")
    parser.add_argument("--keyword", type=str, default=None, help="Keyword filter.")
    parser.add_argument("--limit", type=int, default=100, help="Maximum rows to export.")
    return parser.parse_args()


def main() -> None:
    """Run JSON export."""
    args = parse_args()
    export_entries(
        db_path=args.db,
        output_path=args.out,
        category=args.category,
        keyword=args.keyword,
        limit=args.limit,
    )


if __name__ == "__main__":
    main()

