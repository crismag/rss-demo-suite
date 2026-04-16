"""Level 1 demo: parse a single RSS feed and print a few titles."""

from __future__ import annotations

import argparse
import sys
from pathlib import Path

import feedparser

sys.path.insert(0, str(Path(__file__).resolve().parents[1]))

from shared.utils import load_feeds_config


def main() -> None:
    """Run basic RSS parsing demo."""
    parser = argparse.ArgumentParser(description="Basic RSS parsing demo")
    parser.add_argument("--config", default="feeds.yaml", help="Path to feeds YAML config")
    parser.add_argument("--limit", type=int, default=5, help="Number of entries to print")
    args = parser.parse_args()

    feeds = load_feeds_config(args.config)
    if not feeds:
        print("No feeds configured.")
        return

    feed = feeds[0]
    parsed = feedparser.parse(feed["url"])
    print(f"Feed: {feed['name']} ({feed['category']})")
    for item in parsed.entries[: args.limit]:
        print(f"- {getattr(item, 'title', '(no title)')}")


if __name__ == "__main__":
    main()
