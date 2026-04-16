"""Configuration helpers for loading feed definitions."""

from __future__ import annotations

from pathlib import Path
from typing import Dict, List

import yaml


def load_feeds_config(config_path: str | Path) -> Dict[str, List[str]]:
    """Load feeds grouped by category from a YAML file."""
    path = Path(config_path)
    with path.open("r", encoding="utf-8") as handle:
        data = yaml.safe_load(handle) or {}

    feeds: Dict[str, List[str]] = {}
    for category, urls in data.items():
        feeds[category] = [str(url).strip() for url in (urls or []) if str(url).strip()]
    return feeds

