"""Database utilities for storing and reading RSS entries."""

from __future__ import annotations

import sqlite3
from pathlib import Path
from typing import Iterable

SCHEMA = """
CREATE TABLE IF NOT EXISTS entries (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    feed_name TEXT NOT NULL,
    feed_url TEXT NOT NULL,
    category TEXT NOT NULL,
    title TEXT NOT NULL,
    link TEXT NOT NULL UNIQUE,
    summary TEXT NOT NULL,
    published TEXT,
    ingested_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
"""


def connect_db(db_path: str | Path) -> sqlite3.Connection:
    """Create a SQLite connection with dict-like row access."""
    conn = sqlite3.connect(str(db_path))
    conn.row_factory = sqlite3.Row
    return conn


def init_db(db_path: str | Path) -> None:
    """Initialize the SQLite schema."""
    conn = connect_db(db_path)
    try:
        conn.executescript(SCHEMA)
        conn.commit()
    finally:
        conn.close()


def upsert_entries(db_path: str | Path, entries: Iterable[dict[str, str]]) -> int:
    """Insert feed entries and ignore duplicates by link."""
    conn = connect_db(db_path)
    inserted = 0
    try:
        conn.executemany(
            """
            INSERT OR IGNORE INTO entries
            (feed_name, feed_url, category, title, link, summary, published)
            VALUES (:feed_name, :feed_url, :category, :title, :link, :summary, :published)
            """,
            list(entries),
        )
        inserted = conn.total_changes
        conn.commit()
    finally:
        conn.close()
    return inserted


def fetch_entries(db_path: str | Path, limit: int = 100) -> list[dict]:
    """Fetch entries ordered by ingestion time descending."""
    conn = connect_db(db_path)
    try:
        rows = conn.execute(
            """
            SELECT id, feed_name, feed_url, category, title, link, summary, published, ingested_at
            FROM entries
            ORDER BY ingested_at DESC
            LIMIT ?
            """,
            (limit,),
        ).fetchall()
        return [dict(row) for row in rows]
    finally:
        conn.close()
