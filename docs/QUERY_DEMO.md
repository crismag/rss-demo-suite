# Query Demo

Use the query demo to show how stored entries can be filtered from the command
line.

## Script

```bash
python level2-aggregator/python/query.py --category tech --keyword AI --limit 5 --format text
python level2-aggregator/python/query.py --limit 10 --sort latest --days 7 --format json
```

## Inputs

- `--db`: SQLite database path.
- `--category`: optional category filter.
- `--keyword`: optional text filter.
- `--limit`: maximum number of rows to print.
- `--sort`: output ordering, `latest` or `oldest`.
- `--days`: limit the query to recent entries.
- `--format`: print as `json` or simple text.

## Output

- JSON text or simple text written to the terminal.
