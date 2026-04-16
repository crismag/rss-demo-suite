# Table Sample

This page shows parsed RSS entries in a simple table layout.

## What It Does

- reads exported RSS entries from JSON
- shows the entries in a responsive table
- aggregates counts for entries, feeds, and categories

## What To Do First

Export the tutorial data:

```bash
python scripts/python/export_json.py --out web/table/data/entries.json
```

Or let the page fall back to the current basic sample export at
`web/basic/data/entries.json`.

## What To Observe

- the top summary numbers
- the feed and category pills in the table
- the horizontal scroll behavior on smaller screens

