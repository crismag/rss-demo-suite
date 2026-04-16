"""SQLite helpers for RSS feed and entry storage."""

from __future__ import annotations

import json
import sqlite3
from contextlib import contextmanager
from datetime import datetime, timezone
from pathlib import Path
from typing import Dict, Iterator


def utc_now_iso() -> str:
    """Return current UTC time as ISO string."""
    return datetime.now(timezone.utc).isoformat()


@contextmanager
def get_connection(db_path: str | Path) -> Iterator[sqlite3.Connection]:
    """Yield a SQLite connection with row factory enabled."""
    conn = sqlite3.connect(str(db_path))
    conn.row_factory = sqlite3.Row
    try:
        yield conn
        conn.commit()
    finally:
        conn.close()


def init_db(db_path: str | Path) -> None:
    """Initialize required database tables."""
    with get_connection(db_path) as conn:
        conn.executescript(
            """
            CREATE TABLE IF NOT EXISTS feeds (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                url TEXT NOT NULL UNIQUE,
                category TEXT NOT NULL,
                title TEXT,
                last_fetched TEXT
            );

            CREATE TABLE IF NOT EXISTS entries (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                feed_id INTEGER NOT NULL,
                entry_key TEXT NOT NULL UNIQUE,
                guid TEXT,
                title TEXT,
                link TEXT,
                summary TEXT,
                author TEXT,
                published TEXT,
                published_ts INTEGER,
                categories TEXT,
                ingested_at TEXT NOT NULL,
                FOREIGN KEY (feed_id) REFERENCES feeds(id)
            );

            CREATE INDEX IF NOT EXISTS idx_entries_feed_id ON entries(feed_id);
            CREATE INDEX IF NOT EXISTS idx_entries_published_ts ON entries(published_ts);
            CREATE INDEX IF NOT EXISTS idx_feeds_category ON feeds(category);
            """
        )


def upsert_feed(conn: sqlite3.Connection, url: str, category: str, title: str | None) -> int:
    """Insert or update feed metadata and return feed id."""
    conn.execute(
        """
        INSERT INTO feeds (url, category, title, last_fetched)
        VALUES (?, ?, ?, ?)
        ON CONFLICT(url) DO UPDATE SET
            category = excluded.category,
            title = COALESCE(excluded.title, feeds.title),
            last_fetched = excluded.last_fetched
        """,
        (url, category, title, utc_now_iso()),
    )
    row = conn.execute("SELECT id FROM feeds WHERE url = ?", (url,)).fetchone()
    return int(row["id"])


def insert_entry(conn: sqlite3.Connection, feed_id: int, entry: Dict[str, object]) -> bool:
    """Insert a normalized entry, returning True if inserted."""
    if not entry.get("entry_key"):
        # Skip entries that cannot be deduplicated safely.
        return False

    cursor = conn.execute(
        """
        INSERT OR IGNORE INTO entries (
            feed_id, entry_key, guid, title, link, summary, author,
            published, published_ts, categories, ingested_at
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        """,
        (
            feed_id,
            entry.get("entry_key"),
            entry.get("guid"),
            entry.get("title"),
            entry.get("link"),
            entry.get("summary"),
            entry.get("author"),
            entry.get("published"),
            entry.get("published_ts"),
            json.dumps(entry.get("categories", [])),
            utc_now_iso(),
        ),
    )
    return cursor.rowcount > 0

