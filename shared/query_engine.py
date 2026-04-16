"""Simple query/filter helpers for RSS entries."""

from __future__ import annotations


def filter_entries(
    entries: list[dict], keyword: str | None = None, category: str | None = None, limit: int | None = None
) -> list[dict]:
    """Filter entries by keyword and/or category."""
    result = entries

    if category:
        category_lower = category.lower()
        result = [entry for entry in result if str(entry.get("category", "")).lower() == category_lower]

    if keyword:
        keyword_lower = keyword.lower()
        result = [
            entry
            for entry in result
            if keyword_lower in str(entry.get("title", "")).lower()
            or keyword_lower in str(entry.get("summary", "")).lower()
        ]

    if limit is not None:
        return result[:limit]
    return result
