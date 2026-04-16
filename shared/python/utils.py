"""Shared utility helpers for RSS Demo Suite.

What this module does:
    - loads the YAML feed configuration
    - normalizes parsed RSS entries into storage-ready dictionaries

Inputs:
    - a YAML configuration file path
    - feed metadata and parsed RSS entry objects

Outputs:
    - flattened feed definitions
    - normalized entry dictionaries
"""

from __future__ import annotations

from datetime import datetime, timezone
from pathlib import Path
from typing import Any

import yaml


def _resolve_path(file_path: str | Path) -> Path:
    """Resolve a path against the current directory or the repo root."""
    candidate_path = Path(file_path)
    if candidate_path.is_absolute() or candidate_path.exists():
        return candidate_path

    repo_root = Path(__file__).resolve().parents[2]
    repo_candidate = repo_root / candidate_path
    if repo_candidate.exists():
        return repo_candidate

    return candidate_path


def load_feeds_config(config_path: str | Path) -> list[dict[str, str]]:
    """Load feeds from a YAML file and flatten them into a simple list.

    Purpose:
        Read the tutorial feed list from ``feeds.yaml`` or another YAML file.

    Expected input:
        A path to a YAML file with the repository's ``feeds`` structure.

    Minimal example:
        ``load_feeds_config("feeds.yaml")``

    Output:
        A flat list of feed dictionaries with category, name, and url keys.

    Things to observe:
        The loader can resolve relative paths from the current folder or the
        repository root, which makes the tutorials easier to run.
    """
    resolved_path = _resolve_path(config_path)
    if not resolved_path.exists():
        return []

    data = yaml.safe_load(resolved_path.read_text(encoding="utf-8")) or {}
    feeds_by_category = data.get("feeds", {})

    feeds: list[dict[str, str]] = []
    # Flatten the category-based YAML structure into one list of feeds.
    for category_name, feed_items in feeds_by_category.items():
        for feed_item in feed_items or []:
            feeds.append(
                {
                    "category": str(category_name),
                    "name": str(feed_item.get("name", "Unnamed feed")),
                    "url": str(feed_item.get("url", "")).strip(),
                }
            )
    return [feed for feed in feeds if feed["url"]]


def normalize_entry(
    feed: dict[str, str],
    entry: Any,
) -> dict[str, str]:
    """Normalize a parsed feed entry for database storage."""
    published = ""
    if getattr(entry, "published_parsed", None):
        dt = datetime(*entry.published_parsed[:6], tzinfo=timezone.utc)
        published = dt.isoformat()

    # Convert the RSS entry into the schema expected by the database layer.
    return {
        "feed_name": feed["name"],
        "feed_url": feed["url"],
        "category": feed["category"],
        "title": str(getattr(entry, "title", "")).strip(),
        "link": str(getattr(entry, "link", "")).strip(),
        "summary": str(getattr(entry, "summary", "")).strip(),
        "published": published,
    }
