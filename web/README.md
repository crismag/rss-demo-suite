# Web Samples

The `web/` folder is organized as a small sample gallery.

## Landing Page

- `web/index.html` is the gallery page for the web samples.

## Current Sample

- `web/basic/` contains the current RSS preview page.
- `web/basic/index.html` is the page to open for the live basic sample.
- `web/basic/data/entries.json` is the exported data file used by that page.
- `web/php-dashboard/` contains the PHP dashboard that reads the SQLite
  database and shows the latest ingest run.
- `web/php-dashboard/index.php` is the page to open for the live PHP
  dashboard sample.

## Adding New Samples

Create a new folder for each page sample, for example:

- `web/news/`
- `web/storefront/`
- `web/portfolio/`

Each sample can keep its own `index.html`, `styles.css`, `app.js`, and
`data/` folder so the pages stay isolated from one another.
