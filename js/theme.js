// ================================================
// theme.js — Переключатель светлой и темной темы
// ================================================

document.addEventListener('DOMContentLoaded', () => {
  const root = document.documentElement;
  const nav = document.querySelector('nav');
  const savedTheme = localStorage.getItem('trashstream-theme') || 'dark';

  root.dataset.theme = savedTheme;

  if (!nav) return;

  const button = document.createElement('button');
  button.type = 'button';
  button.className = 'theme-toggle';
  button.setAttribute('aria-label', 'Переключить тему');

  const updateButton = () => {
    const isLight = root.dataset.theme === 'light';
    button.textContent = isLight ? '☀' : '☾';
    button.title = isLight ? 'Светлая тема' : 'Темная тема';
  };

  updateButton();
  nav.appendChild(button);

  button.addEventListener('click', () => {
    root.dataset.theme = root.dataset.theme === 'light' ? 'dark' : 'light';
    localStorage.setItem('trashstream-theme', root.dataset.theme);
    updateButton();
  });
});
