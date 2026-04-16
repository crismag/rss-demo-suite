# Level 3: Insights

This tutorial script shows how to summarize stored RSS titles with a simple
word-frequency pass.

The main `trends.py` script is the one to use. It now includes a small
optional `--days` filter, prints a ranked list with numbering, and shows a
few sample entries that contain the strongest word.

This is still a tutorial script, so the output is intentionally easy to read
instead of highly compressed or optimized.

## Script

```bash
python level3-insights/python/trends.py
python level3-insights/python/trends.py --top 10 --days 7
```

## Inputs

- `--db` for the SQLite database path.
- `--top` for the number of words to display.
- `--days` to narrow the analysis to recent entries only.

## Output

- A ranked list of common title words printed to the terminal.
- A short sample of entry titles that contain the top-ranked word.
