// ================================================
// search.js — Поиск по шоу (только index.html)
// ================================================

const shows = [
  { name: 'Беременна в 16',      url: 'pregnant16.html' },
  { name: 'Беременна по обману', url: 'deceived.html'   },
  { name: 'Мужское / Женское',   url: 'muzhskoe.html'   },
  { name: 'Хата на тата',        url: 'khata.html'      },
  { name: '4 свадьбы',           url: 'weddings.html'   },
  { name: 'Ждули',               url: 'waited.html'     },
];

document.addEventListener('DOMContentLoaded', () => {
  const input = document.getElementById('search-input');
  const results = document.getElementById('search-results');

  if (!input || !results) return;

  input.addEventListener('input', () => {
    const q = input.value.trim().toLowerCase();
    results.innerHTML = '';

    if (!q) { results.style.display = 'none'; return; }

    const filtered = shows.filter(s => s.name.toLowerCase().includes(q));

    if (filtered.length === 0) {
      results.innerHTML = '<div class="search-empty">Ничего не найдено</div>';
    } else {
      filtered.forEach(s => {
        const item = document.createElement('a');
        item.href = s.url;
        item.className = 'search-item';
        item.textContent = s.name;
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
