# RSS Overview

RSS Demo Suite uses a simple feed pipeline:

1. The shared ingestion engine downloads each configured feed.
2. The parser output is normalized into storage-friendly dictionaries.
3. The database layer stores the entries in SQLite and records ingest runs.
4. The query scripts read the stored entries back out for analysis.
5. The export script writes JSON for the basic web sample.
6. The PHP dashboard reads the same SQLite file and shows the latest run.

This setup keeps the tutorial focused on clear data flow rather than hidden
framework behavior.
