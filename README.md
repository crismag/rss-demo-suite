# rss-demo-suite

RSS Demo Suite: From simple feed parsing to a queryable data platform with filtering, aggregation, and insights.

## Quick start

```bash
python -m pip install feedparser pyyaml
python scripts/ingest_feeds.py
python scripts/export_json.py
```
# RSS Demo Suite

A modern, developer-focused demonstration of RSS capabilities — evolving from simple feed parsing into a lightweight data ingestion, querying, and insights platform.
Note. All described below are development targets. In progress

---

## 🚀 Overview

The **RSS Demo Suite** is designed to showcase the power of RSS beyond basic feed reading.

This project demonstrates how RSS can be transformed into:

* Structured datasets
* Queryable content sources
* Aggregated multi-domain feeds
* Insight-ready data pipelines

It is built as a progressive system with multiple levels of capability, making it ideal for learning, experimentation, and portfolio demonstration.

---

## 🧱 Demo Levels

### 🔹 Level 1 — Basic RSS Parsing

* Fetch RSS feeds
* Parse and display entries
* Demonstrates simplicity and accessibility of RSS

---

### 🔹 Level 2 — Aggregation Engine

* Multi-feed ingestion
* SQLite-backed storage
* Deduplication of entries
* Categorized content (tech, sports, finance, church)

---

### 🔹 Level 3 — Query & Filtering Engine

* Query RSS data using:

  * Category filters
  * Keyword search
  * Date ranges
  * Sorting and limits
* CLI and URL-style query support

---

### 🔹 Level 4 — Insights (Experimental)

* Trending topic detection
* Keyword frequency analysis
* Foundation for AI-powered summarization and tagging

---

## 🌐 Live Demo (GitHub Pages)

This repository includes a static web demo powered by exported JSON data.

Features:

* Feed browsing by category
* Search and filtering
* Query simulation
* JSON/API-style data view

---

## ⚙️ Architecture

```
RSS Feeds
   ↓
Ingestion Scripts (Python)
   ↓
SQLite Database
   ↓
Query Engine
   ↓
JSON Export
   ↓
Static Web UI (GitHub Pages)
```

---

## 📂 Repository Structure

```
rss-demo-suite/
├── level1-basic/        # Simple RSS parsing demos
├── level2-aggregator/  # Ingestion + SQLite storage
├── level3-insights/    # Trends and analytics
├── shared/             # Reusable modules (DB, parsing, query engine)
├── scripts/            # Ingestion and export scripts
├── web/                # Static demo UI (GitHub Pages)
├── docs/               # Documentation
├── feeds.yaml          # Feed configuration
└── README.md
```

---

## 🔍 Query Examples

Example query patterns supported:

```
/rss-demo?category=tech&search=AI&limit=5
/rss-demo?days=1&sort=latest
/rss-demo?format=json
```

CLI examples:

```
python query.py --category tech --search AI --limit 5
```

---

## 💡 Why RSS?

RSS remains one of the most powerful, open, and underutilized technologies on the web.

This project demonstrates how RSS can be used to:

* Build content aggregation platforms
* Power automation systems
* Enable structured data pipelines
* Serve as input for AI and analytics systems

---

## 🛠️ Tech Stack

* **Python** — RSS ingestion and processing
* **SQLite** — lightweight structured storage
* **JavaScript / HTML / CSS** — frontend demo UI
* **GitHub Pages** — static hosting

---

## 🚧 Project Status

Active development.

This project is being built as a rapid demonstration and portfolio system, with iterative improvements across multiple levels.

---

## 📌 Future Enhancements

* AI-based summarization and tagging
* Personalized feed generation
* REST API layer
* Social media and external integrations
* Advanced query language (OData-inspired)

---

## 🤝 Contributions

This is a demo-focused project, but contributions, ideas, and improvements are welcome.

---

## 📖 License

MIT License (or your preferred license)
