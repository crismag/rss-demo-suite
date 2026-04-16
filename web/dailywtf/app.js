const ENTRY_SOURCE = 'data/entries.json';
const ENTRY_LIMIT = 12;

function updateStatus(message, state = 'neutral') {
  const statusElement = document.getElementById('status');
  statusElement.textContent = message;
  statusElement.dataset.state = state;
}

function createEntryCard(entry, index) {
  const article = document.createElement('article');
  article.className = 'entry-card';

  const topRow = document.createElement('div');
  topRow.className = 'entry-top';

  const heading = document.createElement('h3');
  heading.className = 'entry-title';

  const link = document.createElement('a');
  link.href = entry.link || '#';
  link.target = '_blank';
  link.rel = 'noopener noreferrer';
  link.textContent = entry.title || '(no title)';
  heading.appendChild(link);

  const category = document.createElement('span');
  category.className = 'category-pill';
  category.textContent = entry.category || 'uncategorized';

  topRow.appendChild(heading);
  topRow.appendChild(category);

  const meta = document.createElement('p');
  meta.className = 'entry-meta';
  meta.textContent = `#${index + 1} • ${entry.published || 'No publish date'}`;

  article.appendChild(topRow);
  article.appendChild(meta);

  if (entry.summary) {
    const summary = document.createElement('p');
    summary.className = 'entry-summary';
    summary.textContent = entry.summary;
    article.appendChild(summary);
  }

  return article;
}

async function loadEntries() {
  const entriesContainer = document.getElementById('entries');
  updateStatus('Loading entries...');

  const response = await fetch(ENTRY_SOURCE, { cache: 'no-store' });
  if (!response.ok) {
    throw new Error(`Unable to load ${ENTRY_SOURCE} (${response.status})`);
  }

  const entries = await response.json();
  entriesContainer.replaceChildren();

  const visibleEntries = entries.slice(0, ENTRY_LIMIT);
  if (visibleEntries.length === 0) {
    const emptyState = document.createElement('p');
    emptyState.className = 'empty-state';
    emptyState.textContent =
      'No Daily WTF entries were exported yet. Add data to preview the page.';
    entriesContainer.appendChild(emptyState);
    updateStatus('No entries found', 'neutral');
    return;
  }

  visibleEntries.forEach((entry, index) => {
    entriesContainer.appendChild(createEntryCard(entry, index));
  });

  updateStatus(`Showing ${visibleEntries.length} entries`, 'ready');
}

loadEntries().catch((error) => {
  console.error(error);
  const entriesContainer = document.getElementById('entries');
  entriesContainer.replaceChildren();

  const errorState = document.createElement('p');
  errorState.className = 'error-state';
  errorState.textContent =
    'Could not load the JSON data. Export Daily WTF entries and refresh this page.';
  entriesContainer.appendChild(errorState);
  updateStatus('Unable to load entries', 'error');
});
