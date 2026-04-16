# Architecture

The project is organized as a tutorial pipeline:

- `level1-basic/php/`: PHP version of the basic RSS parsing walkthrough.
- `level1-basic/python/`: basic RSS parsing walkthrough.
- `level2-aggregator/php/`: PHP version of the aggregation and query
  walkthrough.
- `level2-aggregator/python/`: aggregation and query walkthrough.
- `level3-insights/python/`: title trend walkthrough.
- `scripts/python/`: shared ingestion and export entrypoints.
- `scripts/python/rss_ingestion_engine.py`: shared RSS ingestion engine.
- `shared/php/`: reusable PHP helpers for the tutorial feed loader.
- `shared/php/db.php`: SQLite helpers for the PHP aggregation demo.
- `shared/php/query_engine.php`: filtering helpers for the PHP query demo.
- `shared/python/`: reusable helpers for configuration, storage, and queries.
- `web/basic/`: the current sample static UI.
- `web/index.html`: the gallery landing page for the web samples.
- `web/table/`: the responsive aggregated table sample.
- `web/php-dashboard/`: the PHP dashboard for the shared SQLite data.
- `web/hackernews/`: a mobile-friendly Hacker News story sample.
- `web/bible-votd/`: the Bible Gateway Verse of the Day sample.
- `web/espn/`: the ESPN multi-view sample.
- `web/dailywtf/`: a source-specific sample page for The Daily WTF.
- `web/<sample-name>/`: additional sample pages can follow the same pattern.
