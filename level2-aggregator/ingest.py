"""Level 2 demo: ingest RSS feeds into SQLite."""

import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parents[1]))

from scripts.ingest_feeds import main


if __name__ == "__main__":
    main()
