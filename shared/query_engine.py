"""Lightweight query engine for stored RSS entries."""

from __future__ import annotations

import sqlite3
from datetime import datetime
from pathlib import Path
from typing import Any, Dict, List, Optional


ALLOWED_SORT_FIELDS = {"published_ts", "ingested_at", "title"}
SORT_COLUMN_MAP = {
    "published_ts": "e.published_ts",
    "ingested_at": "e.ingested_at",
    "title": "e.title",
}


def _to_unix(date_value: str) -> int:
    """Convert an ISO date/datetime string to Unix timestamp."""
    normalized = date_value.replace("Z", "+00:00")
    dt = datetime.fromisoformat(normalized)
    return int(dt.timestamp())


def query_entries(
    db_path: str | Path,
    category: Optional[str] = None,
    keyword: Optional[str] = None,
    start_date: Optional[str] = None,
    end_date: Optional[str] = None,
    sort_by: str = "published_ts",
    sort_order: str = "desc",
    limit: int = 20,
) -> List[Dict[str, Any]]:
    """Query RSS entries with optional filters and sorting."""
    if sort_by not in ALLOWED_SORT_FIELDS:
        raise ValueError(f"Unsupported sort field: {sort_by}")

    order = sort_order.lower()
    if order not in {"asc", "desc"}:
        raise ValueError(f"Unsupported sort order: {sort_order}")

    sql = """
        SELECT
            e.id,
            e.title,
            e.link,
            e.summary,
            e.author,
            e.published,
            e.published_ts,
            e.categories,
            e.ingested_at,
            f.url AS feed_url,
            f.category
        FROM entries e
        JOIN feeds f ON f.id = e.feed_id
        WHERE 1=1
    """
    params: List[Any] = []

    if category:
        sql += " AND f.category = ?"
        params.append(category)

    if keyword:
        sql += " AND (e.title LIKE ? OR e.summary LIKE ?)"
        needle = f"%{keyword}%"
        params.extend([needle, needle])

    if start_date:
        sql += " AND e.published_ts >= ?"
        params.append(_to_unix(start_date))

    if end_date:
        sql += " AND e.published_ts <= ?"
        params.append(_to_unix(end_date))

    sort_column = SORT_COLUMN_MAP[sort_by]
    sql += f" ORDER BY {sort_column} {order.upper()} LIMIT ?"
    params.append(max(1, int(limit)))

    with sqlite3.connect(str(db_path)) as conn:
        conn.row_factory = sqlite3.Row
        rows = conn.execute(sql, params).fetchall()
        return [dict(row) for row in rows]
