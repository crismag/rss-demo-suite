async function loadEntries() {
  const response = await fetch('data/entries.json');
  const entries = await response.json();

  const entryList = document.getElementById('entries');
  entryList.innerHTML = '';

  entries.slice(0, 25).forEach((entry) => {
    const listItem = document.createElement('li');
    listItem.innerHTML = `<a href="${entry.link}" target="_blank" rel="noopener">${entry.title}</a> <small>(${entry.category})</small>`;
    entryList.appendChild(listItem);
  });
}

loadEntries().catch((error) => {
  console.error(error);
});
