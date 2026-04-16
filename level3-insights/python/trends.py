"""Level 3 RSS insights demo.

What this script does:
    - loads stored RSS entries from SQLite
    - extracts common words from entry titles
    - optionally filters entries by age
    - prints a simple frequency-based trend list

Usage:
    python level3-insights/python/trends.py --top 10
    python level3-insights/python/trends.py --top 10 --days 1

Inputs:
    - ``--db``: path to the SQLite database file
    - ``--top``: number of trending words to print
    - ``--days``: optional age filter for recent entries only

Outputs:
    - console text showing a ranked list of title words and counts
"""

from __future__ import annotations

import argparse
import re
import sys
from collections import Counter
from datetime import datetime, timedelta, timezone
from pathlib import Path

repo_root = Path(__file__).resolve().parents[2]
sys.path.insert(0, str(repo_root))

from shared.python.db import fetch_entries

STOP_WORDS = {
    "the",
    "and",
    "for",
    "with",
    "from",
    "this",
    "that",
    "into",
    "your",
    "you",
    "are",
    "new",
    "of",
    "to",
    "in",
}

DEFAULT_DATABASE_PATH = "level2-aggregator/db.sqlite"
DEFAULT_TOP_WORD_COUNT = 10
DEFAULT_DAYS = None
ENTRY_FETCH_LIMIT = 1000


def build_parser() -> argparse.ArgumentParser:
    """Create the command-line argument parser for the trends demo."""
    parser = argparse.ArgumentParser(description="Compute simple title trends")
    parser.add_argument(
        "--db",
        default=DEFAULT_DATABASE_PATH,
        help="Path to SQLite database file",
    )
    parser.add_argument(
        "--top",
        type=int,
        default=DEFAULT_TOP_WORD_COUNT,
        help="Top words to show",
    )
    parser.add_argument(
        "--days",
        type=int,
        default=DEFAULT_DAYS,
        help="Show only entries from the last N days",
    )
    return parser


def filter_recent_entries(
    entries: list[dict[str, str]],
    days: int,
) -> list[dict[str, str]]:
    """Return only entries published within the last ``days`` days."""
    if days <= 0:
        return []

    cutoff_time = datetime.now(timezone.utc) - timedelta(days=days)
    recent_entries: list[dict[str, str]] = []

    for entry in entries:
        published_value = entry.get("published", "")
        if not published_value:
            continue

        try:
            published_time = datetime.fromisoformat(published_value)
        except ValueError:
            continue

        if published_time.tzinfo is None:
            published_time = published_time.replace(tzinfo=timezone.utc)

        if published_time >= cutoff_time:
            recent_entries.append(entry)

    return recent_entries


def extract_trending_words(entries: list[dict[str, str]]) -> Counter[str]:
    """Count meaningful words found in entry titles."""
    words: list[str] = []

    # Pull words from each title and skip very short or common stop words.
    for entry in entries:
        title_text = entry.get("title", "")
        for word in re.findall(r"[A-Za-z]{3,}", title_text):
            lowered_word = word.lower()
            if lowered_word not in STOP_WORDS:
                words.append(lowered_word)

    return Counter(words)


def print_sample_entries(
    entries: list[dict[str, str]],
    top_word: str,
    limit: int = 5,
) -> None:
    """Print a few titles that contain the most common word."""
    print("\nSample entries for the top trend:\n")

    shown_count = 0
    for entry in entries:
        title_text = entry.get("title", "")
        if top_word in title_text.lower():
            print(f"- {title_text}")
            shown_count += 1
            if shown_count >= limit:
                break


def main() -> None:
    """Run the title trend tutorial and print the top word counts."""
    parser = build_parser()
    arguments = parser.parse_args()

    print("=" * 50)
    print("LEVEL 3: RSS INSIGHTS - TRENDING WORDS")
    print("=" * 50)

    # Read a larger sample so the trend list has enough material to analyze.
    entries = fetch_entries(arguments.db, limit=ENTRY_FETCH_LIMIT)
    if arguments.days is not None:
        entries = filter_recent_entries(entries, arguments.days)

    print(f"Analyzed entries: {len(entries)}")
    if not entries:
        print("No entries available for analysis.")
        print("=" * 50)
        return

    word_counts = extract_trending_words(entries)

    print("\nTop trending words:\n")

    # Print the most common words in descending order.
    for index, (word, count) in enumerate(
        word_counts.most_common(arguments.top),
        start=1,
    ):
        print(f"{index:2}. {word:<15} {count}")

    if word_counts:
        top_word = word_counts.most_common(1)[0][0]
        print_sample_entries(entries, top_word)

    print("=" * 50)


if __name__ == "__main__":
    main()
