const SOURCE_CANDIDATES = ['data/entries.json', '../basic/data/entries.json'];

function updateStatus(message, state = 'neutral') {
  const statusElement = document.getElementById('status');
  statusElement.textContent = message;
  statusElement.dataset.state = state;
}

function escapeText(value) {
  return value || '';
}

function createTableRow(entry) {
  const row = document.createElement('tr');

  const titleCell = document.createElement('td');
  titleCell.className = 'title-cell';
  const link = document.createElement('a');
  link.href = entry.link || '#';
  link.target = '_blank';
  link.rel = 'noopener noreferrer';
  link.textContent = escapeText(entry.title) || '(no title)';
  titleCell.appendChild(link);

  const feedCell = document.createElement('td');
  const feedPill = document.createElement('span');
  feedPill.className = 'feed-pill';
  feedPill.textContent = escapeText(entry.feed_name) || 'Unknown feed';
  feedCell.appendChild(feedPill);

  const categoryCell = document.createElement('td');
  const categoryPill = document.createElement('span');
  categoryPill.className = 'category-pill';
  categoryPill.textContent = escapeText(entry.category) || 'uncategorized';
  categoryCell.appendChild(categoryPill);

  const publishedCell = document.createElement('td');
  publishedCell.textContent = escapeText(entry.published) || 'No publish date';

  const linkCell = document.createElement('td');
  const linkPill = document.createElement('a');
  linkPill.className = 'link-pill';
  linkPill.href = entry.link || '#';
  linkPill.target = '_blank';
  linkPill.rel = 'noopener noreferrer';
  linkPill.textContent = 'Open';
  linkCell.appendChild(linkPill);

  row.appendChild(titleCell);
  row.appendChild(feedCell);
  row.appendChild(categoryCell);
  row.appendChild(publishedCell);
  row.appendChild(linkCell);

  return row;
}

function updateSummary(entries) {
  const feedCount = new Set(
    entries
      .map((entry) => entry.feed_name || '')
      .filter((value) => value !== '')
  ).size;
  const categoryCount = new Set(
    entries
      .map((entry) => entry.category || '')
      .filter((value) => value !== '')
  ).size;

  document.getElementById('entry-count').textContent = String(entries.length);
  document.getElementById('feed-count').textContent = String(feedCount);
  document.getElementById('category-count').textContent = String(categoryCount);
}

async function loadEntries() {
  let lastError = null;

  for (const source of SOURCE_CANDIDATES) {
    try {
      const response = await fetch(source, { cache: 'no-store' });
      if (!response.ok) {
        throw new Error(`Unable to load ${source} (${response.status})`);
      }

      return response.json();
    } catch (error) {
      lastError = error;
    }
  }

  throw lastError || new Error('Unable to load any table data source.');
}

async function renderTable() {
  const tbody = document.getElementById('entries');
  updateStatus('Loading aggregated entries...');

  const entries = await loadEntries();
  tbody.replaceChildren();

  if (!Array.isArray(entries) || entries.length === 0) {
    const emptyState = document.createElement('tr');
    const cell = document.createElement('td');
    cell.colSpan = 5;
    const message = document.createElement('p');
    message.className = 'empty-state';
    message.textContent = 'No entries were exported yet.';
    cell.appendChild(message);
    emptyState.appendChild(cell);
    tbody.appendChild(emptyState);
    updateStatus('No entries found', 'neutral');
    updateSummary([]);
    return;
  }

  updateSummary(entries);
  entries.slice(0, 50).forEach((entry) => {
    tbody.appendChild(createTableRow(entry));
  });

  updateStatus(`Showing ${Math.min(entries.length, 50)} entries`, 'ready');
}

renderTable().catch((error) => {
  console.error(error);
  const tbody = document.getElementById('entries');
  tbody.replaceChildren();

  const errorRow = document.createElement('tr');
  const errorCell = document.createElement('td');
  errorCell.colSpan = 5;
  const errorState = document.createElement('p');
  errorState.className = 'error-state';
  errorState.textContent =
    'Could not load the exported JSON. Run the export script and refresh this page.';
  errorCell.appendChild(errorState);
  errorRow.appendChild(errorCell);
  tbody.appendChild(errorRow);
  updateStatus('Unable to load entries', 'error');
  document.getElementById('entry-count').textContent = '0';
  document.getElementById('feed-count').textContent = '0';
  document.getElementById('category-count').textContent = '0';
});
