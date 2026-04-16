"""Level 1 RSS parsing demo using a real-world feed.

What this script does:
    - selects a preset RSS source or a custom RSS URL
    - downloads and parses that RSS feed
    - prints a very small preview of the entry titles

Usage:
    python level1-basic/python/demo_realworld.py
    python level1-basic/python/demo_realworld.py --source hackernews
    python level1-basic/python/demo_realworld.py --url https://example.com/feed
    python level1-basic/python/demo_realworld.py --limit 5

Inputs:
    - ``--source``: choose a preset RSS source
    - ``--url``: override the feed URL with a custom RSS source
    - ``--limit``: maximum number of entry titles to display

Outputs:
    - console text showing the selected feed and a short title preview

Things to observe:
    - the default preview is short so the first run is easy to read
    - a preset source can be swapped for a custom RSS URL
    - the script shows how a beginner can move from URL to output step by step
    - the output includes numbered entries so it is easier to scan
"""

from __future__ import annotations

import argparse
from pathlib import Path

try:
    import feedparser
except ModuleNotFoundError:  # pragma: no cover - tutorial safety net
    feedparser = None

DEFAULT_SOURCE_KEY = "dailywtf"
DEFAULT_ENTRY_LIMIT = 2

RSS_SOURCES: dict[str, dict[str, str]] = {
    "dailywtf": {
        "name": "The Daily WTF",
        "url": "https://feeds.feedburner.com/TheDailyWtf",
    },
    "hackernews": {
        "name": "Hacker News",
        "url": "https://hnrss.org/frontpage",
    },
    "espn": {
        "name": "ESPN Top Headlines",
        "url": "https://www.espn.com/espn/rss/news",
    },
}


def build_parser() -> argparse.ArgumentParser:
    """Build the command-line parser for the tutorial script.

    Purpose:
        Define the small command-line interface used by the demo.

    Expected input:
        No direct runtime values. The parser defines:
        - ``--source`` for a preset RSS source key
        - ``--url`` for a custom RSS feed URL
        - ``--limit`` for the number of titles to print

    Minimal example:
        ``python level1-basic/python/demo_realworld.py --source hackernews``

    Output:
        An ``ArgumentParser`` instance ready to read CLI arguments.

    Things to observe:
        The defaults keep the demo short and beginner-friendly.
    """
    parser = argparse.ArgumentParser(
        description="Basic RSS parsing demo for a real-world source",
    )
    parser.add_argument(
        "--source",
        choices=sorted(RSS_SOURCES),
        default=DEFAULT_SOURCE_KEY,
        help="Preset RSS source to use",
    )
    parser.add_argument(
        "--url",
        default=None,
        help="Custom RSS feed URL to use instead of a preset",
    )
    parser.add_argument(
        "--limit",
        type=int,
        default=DEFAULT_ENTRY_LIMIT,
        help="Number of entry titles to print",
    )
    return parser


def resolve_source(source_key: str, custom_url: str | None) -> tuple[str, str]:
    """Resolve which RSS feed should be used.

    Purpose:
        Turn a preset source name or a custom URL into a feed label and URL.

    Expected input:
        - ``source_key`` should match one of the keys in ``RSS_SOURCES``
        - ``custom_url`` may be ``None`` or a direct RSS feed URL

    Minimal example:
        ``resolve_source("dailywtf", None)`` returns The Daily WTF feed.

    Output:
        A ``(feed_name, feed_url)`` tuple that can be passed to the preview
        function.

    Things to observe:
        A custom URL overrides the preset list so you can test any RSS source.
        This keeps the demo flexible without adding extra menu logic.
    """
    if custom_url:
        return "Custom source", custom_url

    selected_source = RSS_SOURCES[source_key]
    return selected_source["name"], selected_source["url"]


def print_feed_preview(
    feed_name: str,
    feed_url: str,
    entry_limit: int,
) -> None:
    """Fetch a feed and print a beginner-friendly preview.

    Purpose:
        Download RSS XML, extract titles, and show the first few entries.

    Expected input:
        - ``feed_name`` is a human-friendly label for the source
        - ``feed_url`` is the RSS URL to download
        - ``entry_limit`` controls how many titles are shown

    Minimal example:
        ``print_feed_preview("The Daily WTF", "https://...", 2)``

    Output:
        Console text with a header, numbered titles, and a closing separator.

    Things to observe:
        - the preview stays small on purpose
        - errors are handled so the script does not crash on a bad feed
        - entries are numbered so the output is easier to scan
        - the helper prints feedback even when the feed is empty or broken

    Developer note:
        The fetch, error handling, and formatting logic stay in one place so
        the demo is easy to follow and easy to extend later.
    """
    if feedparser is None:
        print("=" * 50)
        print(f"Feed: {feed_name}")
        print(f"URL: {feed_url}")
        print("=" * 50)
        print("Missing dependency: feedparser")
        print("Install it with: python -m pip install feedparser")
        print("=" * 50)
        return

    try:
        # Parse the RSS URL and keep the tutorial example intentionally small.
        parsed_feed = feedparser.parse(feed_url)
    except Exception as error:  # pragma: no cover - tutorial safety net
        print("=" * 50)
        print(f"Feed: {feed_name}")
        print(f"URL: {feed_url}")
        print("=" * 50)
        print(f"Could not read feed: {error}")
        print("=" * 50)
        return

    if getattr(parsed_feed, "bozo", False):
        bozo_error = getattr(parsed_feed, "bozo_exception", None)
        print("=" * 50)
        print(f"Feed: {feed_name}")
        print(f"URL: {feed_url}")
        print("=" * 50)
        print(f"Feed parsing warning: {bozo_error}")
        print("=" * 50)
        return

    # Show which source was selected so the output is easy to follow.
    print("=" * 50)
    print(f"Feed: {feed_name}")
    print(f"URL: {feed_url}")
    print("=" * 50)

    # Print a short list of titles to demonstrate basic RSS access.
    if not parsed_feed.entries:
        print("No entries found.")
        print("=" * 50)
        return

    for idx, feed_entry in enumerate(
        parsed_feed.entries[:entry_limit],
        start=1,
    ):
        entry_title = getattr(feed_entry, "title", "(no title)")
        print(f"{idx}. {entry_title}")

    print("=" * 50)


def main() -> None:
    """Run the real-world RSS tutorial script.

    Purpose:
        Glue together argument parsing, source selection, and preview output.

    Expected input:
        Command-line options from the user, such as ``--source`` or ``--url``.

    Minimal example:
        ``python level1-basic/python/demo_realworld.py``

    Output:
        A formatted preview of RSS entry titles, or a friendly error message.

    Things to observe:
        This function keeps the top-level flow short so the demo is easy to
        trace from input to output.

    Developer note:
        The main function stays small on purpose so the tutorial remains easy
        to compare with the PHP version and other Level 1 scripts.
    """
    parser = build_parser()
    arguments = parser.parse_args()

    # Keep the demo focused while still allowing a quick source override.
    try:
        feed_name, feed_url = resolve_source(arguments.source, arguments.url)
    except KeyError:
        print(f"Unknown source: {arguments.source}")
        return

    print_feed_preview(feed_name, feed_url, arguments.limit)


if __name__ == "__main__":
    main()
