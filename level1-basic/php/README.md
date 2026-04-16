# Level 1: Basic Parsing in PHP

This tutorial folder contains two small RSS demos. Start with `demo.php` first
so you can learn the basic flow before trying the real-world example.

Both scripts keep the workflow intentionally small so the steps are easy to
follow.

Before running the demos, install the SimplePie dependency:

```bash
composer require simplepie/simplepie
```

## Run It

```bash
php level1-basic/php/demo.php
```

Optional arguments:

```bash
php level1-basic/php/demo.php --config feeds.yaml --limit 5
```

## Original Demo

`demo.php` loads the sample feed list, picks the first feed, and prints a few
entry titles.

Important code sections:

- `buildArguments()` creates the command-line argument parser and applies the
  default values.
- `loadFeedsConfig()` in `shared/php/utils.php` reads the YAML feed file and
  flattens it into a simple list of feeds.
- `main()` coordinates the flow: load feeds, choose the first one, and print
  the preview.
- `printFeedPreview()` shows the selected feed name and category, then prints
  the first few titles.
- `fetchEntryTitles()` downloads the RSS URL and extracts entry titles from
  the feed XML.

What to observe:

- The script always starts with the first feed in `feeds.yaml`.
- The terminal output shows the feed name and category before the titles.
- If a feed cannot be read, the script prints `No entries found.`
- The `--limit` option changes how many titles are displayed.

## Try The Real-World Example

After you understand `demo.php`, try `demo_realworld.php`. It uses the same
Level 1 pattern, but points at a real-world RSS feed by default.

Run it with:

```bash
php level1-basic/php/demo_realworld.php
```

Optional arguments:

```bash
php level1-basic/php/demo_realworld.php --source hackernews
php level1-basic/php/demo_realworld.php --url https://example.com/feed
php level1-basic/php/demo_realworld.php --limit 5
```

Important code sections:

- `DEFAULT_SOURCE_KEY` sets the default preset feed.
- `DEFAULT_ENTRY_LIMIT` controls the default number of titles printed.
- `RSS_SOURCES` holds the preset feeds you can choose with `--source`.
- `resolveSource()` picks either a preset source or a custom `--url`.
- `buildArguments()` creates the command-line options for source, URL, and
  limit.
- `printFeedPreview()` shows the selected feed and prints the first few
  titles.
- `SimplePie` downloads and parses the RSS XML from the selected feed URL.

What to observe:

- The default feed is The Daily WTF.
- `--source` switches between the preset real-world RSS feeds.
- `--url` lets you point the demo at any RSS feed you want.
- `--limit` is set to `2` by default so the preview stays short.
- The output shows both the selected feed name and the feed URL.

What happens:

1. The script reads the command-line options.
2. It resolves the feed source from the preset list or the custom URL.
3. It downloads the RSS XML with SimplePie.
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
