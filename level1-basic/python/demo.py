"""Level 1 RSS parsing demo.

What this script does:
    - loads the feed configuration from ``feeds.yaml``
    - selects the first configured feed
    - downloads and parses that RSS feed
    - prints a small set of entry titles to the terminal

Usage:
    python level1-basic/python/demo.py
    python level1-basic/python/demo.py --config feeds.yaml --limit 5

Inputs:
    - ``--config``: path to the YAML feed configuration file
    - ``--limit``: maximum number of feed titles to display

Outputs:
    - console text showing the feed name, category, and entry titles
"""

from __future__ import annotations

import argparse
from pathlib import Path

try:
    import feedparser
except ModuleNotFoundError:  # pragma: no cover - tutorial safety net
    feedparser = None

from shared.python.utils import load_feeds_config

DEFAULT_CONFIG_PATH = "feeds.yaml"
DEFAULT_ENTRY_LIMIT = 5


def build_parser() -> argparse.ArgumentParser:
    """Create the command-line argument parser for the demo."""
    parser = argparse.ArgumentParser(description="Basic RSS parsing demo")
    parser.add_argument(
        "--config",
        default=DEFAULT_CONFIG_PATH,
        help="Path to feeds YAML config",
    )
    parser.add_argument(
        "--limit",
        type=int,
        default=DEFAULT_ENTRY_LIMIT,
        help="Number of entry titles to print",
    )
    return parser


def print_feed_preview(
    feed_definition: dict[str, str],
    entry_limit: int,
) -> None:
    """Download one feed and print a short preview of its entries."""
    if feedparser is None:
        print("Missing dependency: feedparser")
        print("Install it with: python -m pip install feedparser")
        return

    # Parse the configured RSS URL and keep the example intentionally small.
    parsed_feed = feedparser.parse(feed_definition["url"])

    # Show which feed was selected so the output is easy to follow.
    feed_name = feed_definition["name"]
    feed_category = feed_definition["category"]
    print(f"Feed: {feed_name} ({feed_category})")

    # Print a short list of titles to demonstrate basic RSS access.
    for feed_entry in parsed_feed.entries[:entry_limit]:
        entry_title = getattr(feed_entry, "title", "(no title)")
        print(f"- {entry_title}")


def main() -> None:
    """Run the basic RSS parsing tutorial script."""
    parser = build_parser()
    arguments = parser.parse_args()

    # Load the feed list from the YAML file.
    feed_definitions = load_feeds_config(arguments.config)
    if not feed_definitions:
        print("No feeds configured.")
        return

    # Keep the demo focused by using the first configured feed only.
    selected_feed = feed_definitions[0]
    print_feed_preview(selected_feed, arguments.limit)


if __name__ == "__main__":
    main()
