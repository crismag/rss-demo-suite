"""Simple query/filter helpers for RSS entries.

What this module does:
    - filters stored RSS entries by category
    - filters entries by keyword in title or summary
    - limits the final result list

Inputs:
    - a list of entry dictionaries
    - optional keyword, category, and limit values

Outputs:
    - a filtered list of entry dictionaries
"""

from __future__ import annotations

from collections.abc import Sequence


def filter_entries(
    entries: Sequence[dict[str, str]],
    keyword: str | None = None,
    category: str | None = None,
    limit: int | None = None,
) -> list[dict[str, str]]:
    """Filter entries by keyword and/or category."""
    filtered_entries = list(entries)

    if category:
        category_lower = category.lower()
        filtered_entries = [
            entry
            for entry in filtered_entries
            if str(entry.get("category", "")).lower() == category_lower
        ]

    if keyword:
        keyword_lower = keyword.lower()
        filtered_entries = [
            entry
            for entry in filtered_entries
            if keyword_lower in str(entry.get("title", "")).lower()
            or keyword_lower in str(entry.get("summary", "")).lower()
        ]

    if limit is not None:
        return filtered_entries[:limit]
    return filtered_entries
