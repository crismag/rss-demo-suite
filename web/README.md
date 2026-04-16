# Web Samples

The `web/` folder is organized as a small sample gallery.

## Landing Page

- `web/index.html` is the gallery page for the web samples.
- `web/styles.css` holds the shared gallery styling for the landing page.

## Current Sample

- `web/basic/` contains the current RSS preview page.
- `web/basic/index.html` is the page to open for the live basic sample.
- `web/basic/data/entries.json` is the exported data file used by that page.
- `web/basic/app.js` loads the exported JSON and renders the entries safely.
- `web/table/` contains a responsive table view for aggregated entries.
- `web/table/index.html` is the page to open for the table sample.
- `web/table/data/entries.json` is the dedicated export file for the table
  sample.
- `web/hackernews/` contains a source-specific sample page for Hacker News.
- `web/hackernews/index.php` is the page to open for that story sample.
- `web/php-dashboard/` contains the PHP dashboard that reads the SQLite
  database and shows the latest ingest run.
- `web/php-dashboard/index.php` is the page to open for the live PHP
  dashboard sample.
- `web/php-dashboard/styles.css` contains the dashboard styling.
- `web/bible-votd/` contains the Bible Gateway Verse of the Day sample.
- `web/bible-votd/index.php` is the page to open for that verse sample.
- `web/espn/` contains the ESPN multi-view sample.
- `web/espn/index.php` is the page to open for that sports sample.
- `web/dailywtf/` contains a source-specific sample page for The Daily WTF.
- `web/dailywtf/index.html` is the page to open for that source sample.
- `web/dailywtf/data/entries.json` is where source-specific exported data can
  live for the sample.

## Adding New Samples

Create a new folder for each page sample, for example:

- `web/news/`
- `web/storefront/`
- `web/portfolio/`
- `web/table/`
- `web/hackernews/`
- `web/bible-votd/`
- `web/espn/`
- `web/dailywtf/`

Each sample can keep its own `index.html`, `styles.css`, `app.js`, and
`data/` folder so the pages stay isolated from one another.

## What To Notice

- the gallery page uses a warmer visual style and clearly separates each
  sample
- the basic page focuses on simple exported data and safe browser rendering
- the table page shows aggregation in a compact responsive layout
- the Hacker News page focuses on headline density and quick scanning
- the PHP dashboard focuses on the latest ingest run and recent stored data
- the Bible sample focuses on a single mobile-friendly reading card
- the ESPN sample focuses on multiple stories and image-aware cards
- each web sample stays self-contained so future source-specific demos can be
  added without disturbing the others
