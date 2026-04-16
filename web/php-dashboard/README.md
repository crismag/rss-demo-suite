# PHP Web Dashboard

This folder contains a small PHP web page that reads the RSS demo SQLite
database and shows the most recent ingest run plus the newest stored entries.

## What This Page Does

- reads `level2-aggregator/db.sqlite`
- shows the latest recorded ingest time
- shows how many feeds and entries were handled in that run
- lists the newest stored RSS items
- can optionally trigger the ingest script again when the data is stale or
  when you press the refresh button

## How To Run It

Use the PHP built-in server from the repository root:

```bash
php -S localhost:8000 -t web
```

Then open:

```text
http://localhost:8000/php-dashboard/
```

## What To Do First

Run the Level 2 ingest script before opening the dashboard:

```bash
php level2-aggregator/php/ingest.php
```

That script fills the shared SQLite database. The dashboard then reads the
same data and shows it on screen.

## Important Code Sections

- `fetchLatestIngestRun()` reads the newest ingest summary row from SQLite.
- `shouldRefreshAutomatically()` decides whether the page should refresh old
  data.
- `refreshData()` runs the ingest script through the PHP command line.
- `fetchEntries()` gets the newest stored RSS entries for display.
- the HTML section renders summary cards and a recent-entry list.

## What To Observe

- the latest ingest time should update after the ingest script runs
- the summary cards should show the number of feeds, parsed entries, and
  inserted rows
- the recent entries should reflect the newest stored RSS items
- if data is old enough, the page will try to refresh it automatically

## Useful Notes

- the dashboard uses the same SQLite file as the Level 2 PHP ingest demo
- the refresh button is handy when you want to pull fresh feed data on demand
- `?refresh=1` in the URL does the same thing as the button
- the page treats data older than about one hour as stale and tries to
  refresh it automatically
- if `shell_exec()` is disabled on your server, the page will still show the
  existing database contents but it cannot launch the ingest script
- this page is intentionally small so it can be used as a teaching example
