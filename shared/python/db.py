"""SQLite helpers for RSS Demo Suite.

What this module does:
    - creates and maintains the SQLite schema used by the tutorial
    - inserts normalized RSS entries while ignoring duplicates
    - fetches the newest stored entries for query and export scripts

Inputs:
    - a filesystem path to the SQLite database file
    - normalized entry dictionaries produced by the ingestion layer

Outputs:
    - SQLite tables and rows on disk
    - Python dictionaries when fetching stored entries
"""

from __future__ import annotations

import hashlib
import sqlite3
from pathlib import Path

SCHEMA = """
CREATE TABLE IF NOT EXISTS entries (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    entry_key TEXT NOT NULL UNIQUE,
    feed_name TEXT NOT NULL,
    feed_url TEXT NOT NULL,
    category TEXT NOT NULL,
    title TEXT NOT NULL,
    link TEXT NOT NULL,
    summary TEXT NOT NULL,
    published TEXT NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_entries_category ON entries(category);
CREATE INDEX IF NOT EXISTS idx_entries_published ON entries(published);
"""


def _connect(db_path: str | Path) -> sqlite3.Connection:
    """Open a SQLite connection and create the parent folder if needed."""
    path = Path(db_path)
    path.parent.mkdir(parents=True, exist_ok=True)
    conn = sqlite3.connect(path)
    conn.row_factory = sqlite3.Row
    return conn


def _entry_key(entry: dict[str, str]) -> str:
    """Build a stable duplicate-detection key for one entry."""
    key_fields = ("feed_url", "link", "title", "published")
    payload = "|".join(
        str(entry.get(field, "")).strip()
        for field in key_fields
    )
    return hashlib.sha256(payload.encode("utf-8")).hexdigest()


def init_db(db_path: str | Path) -> None:
    """Create the SQLite schema if it does not already exist."""
    with _connect(db_path) as conn:
        conn.executescript(SCHEMA)


def upsert_entries(db_path: str | Path, entries: list[dict[str, str]]) -> int:
    """Insert parsed entries while ignoring duplicates."""
    init_db(db_path)

    inserted = 0
    with _connect(db_path) as conn:
        for entry in entries:
            entry_key = _entry_key(entry)
            cursor = conn.execute(
                """
                INSERT OR IGNORE INTO entries (
                    entry_key, feed_name, feed_url, category,
                    title, link, summary, published
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                """,
                (
                    entry_key,
                    str(entry.get("feed_name", "")).strip(),
                    str(entry.get("feed_url", "")).strip(),
                    str(entry.get("category", "")).strip(),
                    str(entry.get("title", "")).strip(),
                    str(entry.get("link", "")).strip(),
                    str(entry.get("summary", "")).strip(),
                    str(entry.get("published", "")).strip(),
                ),
            )
            inserted += cursor.rowcount
    return inserted


def fetch_entries(
    db_path: str | Path,
    limit: int = 200,
) -> list[dict[str, str]]:
    """Return the newest entries from SQLite."""
    init_db(db_path)

    if limit <= 0:
        return []

    with _connect(db_path) as conn:
        rows = conn.execute(
            """
            SELECT feed_name, feed_url, category, title,
                   link, summary, published
            FROM entries
            ORDER BY published DESC, id DESC
            LIMIT ?
            """,
            (limit,),
        ).fetchall()

    return [dict(row) for row in rows]
