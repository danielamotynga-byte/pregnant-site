// ================================================
// nav-active.js — Активная ссылка в навбаре (все страницы)
// ================================================

document.addEventListener('DOMContentLoaded', () => {
  const currentFile = window.location.pathname.split('/').pop() || 'index.html';

  const links = document.querySelectorAll('nav a');
  links.forEach(link => {
    const href = link.getAttribute('href');
    if (href === currentFile) {
      link.classList.add('nav-active');
    }
  });
});
