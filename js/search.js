// ================================================
// search.js — Поиск по шоу (только index.html)
// ================================================

document.addEventListener('DOMContentLoaded', async () => {
  const input = document.getElementById('search-input');
  const results = document.getElementById('search-results');
  
  if (!input || !results) return;

  // Загрузить данные через AJAX
  const shows = await window.getTrashstreamShows();

  input.addEventListener('input', () => {
    const q = input.value.trim().toLowerCase();
    results.innerHTML = '';

    if (!q) { results.style.display = 'none'; return; }

    const filtered = shows.filter(s => s.title.toLowerCase().includes(q));

    if (filtered.length === 0) {
      results.innerHTML = '<div class="search-empty">Ничего не найдено</div>';
    } else {
      filtered.forEach(s => {
        const item = document.createElement('a');
        item.href = s.link;
        item.className = 'search-item';
        item.textContent = s.title;
        results.appendChild(item);
      });
    }

    results.style.display = 'block';
  });

  // Закрыть при клике вне поиска
  document.addEventListener('click', (e) => {
    if (!input.contains(e.target) && !results.contains(e.target)) {
      results.style.display = 'none';
    }
  });
});
