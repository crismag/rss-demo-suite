"""Level 3 demo: show basic trending words from feed titles."""

from __future__ import annotations

import argparse
import re
import sys
from collections import Counter
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parents[1]))

from shared.db import fetch_entries

STOP_WORDS = {"the", "and", "for", "with", "from", "this", "that", "into", "your", "you", "are", "new"}


def main() -> None:
    """Print top words from entry titles."""
    parser = argparse.ArgumentParser(description="Compute simple title trends")
    parser.add_argument("--db", default="level2-aggregator/db.sqlite", help="Path to SQLite database file")
    parser.add_argument("--top", type=int, default=10, help="Top words to show")
    args = parser.parse_args()

    entries = fetch_entries(args.db, limit=1000)
    words: list[str] = []
    for entry in entries:
        words.extend(
            word.lower()
            for word in re.findall(r"[A-Za-z]{3,}", entry.get("title", ""))
            if word.lower() not in STOP_WORDS
        )

    for word, count in Counter(words).most_common(args.top):
        print(f"{word}: {count}")


if __name__ == "__main__":
    main()
