"""RSS parsing helpers built on feedparser."""

from __future__ import annotations

from datetime import datetime, timezone
from time import mktime
from typing import Any, Dict, List, Optional

import feedparser


def _to_iso_datetime(struct_time_obj: Any) -> Optional[str]:
    """Convert a feedparser time struct to ISO timestamp."""
    if not struct_time_obj:
        return None
    timestamp = int(mktime(struct_time_obj))
    return datetime.fromtimestamp(timestamp, tz=timezone.utc).isoformat()


def parse_feed(url: str) -> Dict[str, Any]:
    """Fetch and parse a single RSS/Atom feed URL."""
    parsed = feedparser.parse(url)
    return {
        "url": url,
        "title": parsed.feed.get("title"),
        "entries": [_normalize_entry(entry) for entry in parsed.entries],
        "bozo": getattr(parsed, "bozo", 0),
    }


def _normalize_entry(entry: Any) -> Dict[str, Any]:
    """Normalize feed entry fields for storage and querying."""
    guid = entry.get("id") or entry.get("guid")
    link = entry.get("link")
    key = guid or link

    published_struct = entry.get("published_parsed") or entry.get("updated_parsed")
    published_iso = _to_iso_datetime(published_struct)
    published_unix = int(mktime(published_struct)) if published_struct else None

    tags: List[str] = []
    for tag in entry.get("tags", []):
        term = tag.get("term")
        if term:
            tags.append(str(term))

    return {
        "entry_key": key,
        "guid": guid,
        "title": entry.get("title"),
        "link": link,
        "summary": entry.get("summary"),
        "author": entry.get("author"),
        "published": published_iso,
        "published_ts": published_unix,
        "categories": tags,
    }

