"""Level 2 RSS ingestion wrapper.

What this script does:
    - calls the shared ingestion workflow used by the tutorial scripts
    - keeps the level 2 example small and easy to run

Usage:
    python level2-aggregator/python/ingest_feeds.py

Inputs:
    - no direct command-line arguments
    - the shared ingest script reads ``feeds.yaml`` and writes SQLite data

Outputs:
    - console output from the shared ingestion script
"""

from __future__ import annotations

import sys
from pathlib import Path

# Add the repository root so shared Python helpers can be imported directly.
repo_root = Path(__file__).resolve().parents[2]
sys.path.insert(0, str(repo_root))

from scripts.python.rss_ingestion_engine import main


def run_demo() -> None:
    """Run the level 2 ingestion example."""
    # Reuse the shared ingestion CLI so the tutorial stays focused.
    main()


if __name__ == "__main__":
    run_demo()
