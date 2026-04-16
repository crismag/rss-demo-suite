# ESPN Multi-View Sample

This page reads a few official ESPN RSS feeds and presents them in a richer
multi-view layout.

## What It Does

- fetches multiple ESPN feeds server-side
- shows a featured story at the top
- shows feed-specific cards underneath
- shows a compact combined headline list
- tries to display feed images when the feed provides them

## Feeds Used

- Top Headlines
- NFL
- NBA
- Soccer

## What To Observe

- the page uses a dark sports-style layout
- the image cards shrink nicely on smaller screens
- each feed section keeps its own identity while still feeling like one page
- the bottom list gives a quick scan view for mobile users

## Useful Notes

- ESPN RSS terms require that content remain linked back to ESPN
- if a feed item does not include an image, the page falls back to a styled
  placeholder
- you can add more feed sections later by extending the feed list in the page

