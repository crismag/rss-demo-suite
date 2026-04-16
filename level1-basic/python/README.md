# Level 1: Basic Parsing in Python

This tutorial folder contains two small RSS demos. Start with `demo.py` first
so you can learn the basic flow before trying the real-world example.

Both scripts keep the workflow intentionally small so the steps are easy to
follow.

Before running the demos, install the RSS parsing dependency:

```bash
python -m pip install feedparser
```

## Run It

```bash
python level1-basic/python/demo.py
```

Optional arguments:

```bash
python level1-basic/python/demo.py --config feeds.yaml --limit 5
```

## Original Demo

`demo.py` loads the sample feed list, picks the first feed, and prints a few
entry titles.

Important code sections:

- `build_parser()` creates the command-line argument parser and applies the
  default values.
- `load_feeds_config()` in `shared/python/utils.py` reads the YAML feed file
  and flattens it into a simple list of feeds.
- `main()` coordinates the flow: load feeds, choose the first one, and print
  the preview.
- `print_feed_preview()` shows the selected feed name and category, then
  prints the first few titles.
- `feedparser.parse()` downloads and parses the RSS XML from the selected
  feed URL.

What to observe:

- The script always starts with the first feed in `feeds.yaml`.
- The terminal output shows the feed name and category before the titles.
- If no feeds are configured, the script prints `No feeds configured.`
- The `--limit` option changes how many titles are displayed.

## Try The Real-World Example

After you understand `demo.py`, try `demo_realworld.py`. It uses the same
Level 1 pattern, but points at a real-world RSS feed by default.

Run it with:

```bash
python level1-basic/python/demo_realworld.py
```

Optional arguments:

```bash
python level1-basic/python/demo_realworld.py --source hackernews
python level1-basic/python/demo_realworld.py --url https://example.com/feed
python level1-basic/python/demo_realworld.py --limit 5
```

Important code sections:

- `DEFAULT_SOURCE_KEY` sets the default preset feed.
- `DEFAULT_ENTRY_LIMIT` controls the default number of titles printed.
- `RSS_SOURCES` holds the preset feeds you can choose with `--source`.
- `resolve_source()` picks either a preset source or a custom `--url`.
- `build_parser()` creates the command-line options for source, URL, and
  limit.
- `print_feed_preview()` shows the selected feed and prints the first few
  titles.
- `feedparser.parse()` downloads and parses the RSS XML from the selected
  feed URL.

What to observe:

- The default feed is The Daily WTF.
- `--source` switches between the preset real-world RSS feeds.
- `--url` lets you point the demo at any RSS feed you want.
- `--limit` is set to `2` by default so the preview stays short.
- The output shows both the selected feed name and the feed URL.

What happens:

1. The script reads the command-line options.
2. It resolves the feed source from the preset list or the custom URL.
3. It downloads the RSS XML from that feed URL.
4. It reads entry titles from the feed.
5. It prints a short preview to the terminal.

What else can be done:

- Change `--limit` to show a different number of titles.
- Use `--source` to compare the built-in sample feeds.
- Use `--url` to test a feed that is not listed in the presets.
- Add more output lines to print links, summaries, or publication dates.
- Expand the demo later to let the user choose a feed category instead of
  always using a preset or custom URL.

## Output

- Feed metadata and a short list of entry titles printed to the terminal.
