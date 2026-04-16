"""Shared utility helpers for RSS Demo Suite."""

from __future__ import annotations

from datetime import datetime, timezone
from pathlib import Path
from typing import Any

import yaml


def load_feeds_config(config_path: str | Path) -> list[dict[str, str]]:
    """Load feeds from a YAML file and flatten them into a simple list."""
    data = yaml.safe_load(Path(config_path).read_text(encoding="utf-8")) or {}
    feeds_by_category = data.get("feeds", {})

    feeds: list[dict[str, str]] = []
    for category, items in feeds_by_category.items():
        for item in items or []:
            feeds.append(
                {
                    "category": str(category),
                    "name": str(item.get("name", "Unnamed feed")),
                    "url": str(item.get("url", "")).strip(),
                }
            )
    return [feed for feed in feeds if feed["url"]]


def normalize_entry(feed: dict[str, str], entry: dict[str, Any]) -> dict[str, str]:
    """Normalize a parsed feed entry for database storage."""
    published = ""
    if getattr(entry, "published_parsed", None):
        dt = datetime(*entry.published_parsed[:6], tzinfo=timezone.utc)
        published = dt.isoformat()

    return {
        "feed_name": feed["name"],
        "feed_url": feed["url"],
        "category": feed["category"],
        "title": str(getattr(entry, "title", "")).strip(),
        "link": str(getattr(entry, "link", "")).strip(),
        "summary": str(getattr(entry, "summary", "")).strip(),
        "published": published,
    }
