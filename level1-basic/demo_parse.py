"""Level 1 demo: parse configured feeds and print top entries."""

from __future__ import annotations

from pathlib import Path

from shared.config import load_feeds_config
from shared.rss import parse_feed


def main() -> None:
    """Parse one feed per category and print sample entries."""
    root = Path(__file__).resolve().parents[1]
    feeds = load_feeds_config(root / "feeds.yaml")

    for category, urls in feeds.items():
        if not urls:
            continue
        parsed = parse_feed(urls[0])
        print(f"\n[{category}] {parsed.get('title') or parsed['url']}")
        for entry in parsed["entries"][:3]:
            print(f"- {entry.get('title')}")


if __name__ == "__main__":
    main()

