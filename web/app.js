async function loadEntries() {
  const response = await fetch('data/entries.json');
  const entries = await response.json();

  const list = document.getElementById('entries');
  list.innerHTML = '';

  entries.slice(0, 25).forEach((entry) => {
    const item = document.createElement('li');
    item.innerHTML = `<a href="${entry.link}" target="_blank" rel="noopener">${entry.title}</a> <small>(${entry.category})</small>`;
    list.appendChild(item);
  });
}

loadEntries().catch((error) => {
  console.error(error);
});
