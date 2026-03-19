// ================================================
// animations.js — Анимации при прокрутке (все страницы)
// ================================================

document.addEventListener('DOMContentLoaded', () => {
  // Добавляем класс animate всем нужным элементам
  const targets = document.querySelectorAll('h1, h2, p, li, tr, .show-poster, table');

  targets.forEach((el, i) => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(24px)';
    el.style.transition = `opacity 0.5s ease ${i * 0.04}s, transform 0.5s ease ${i * 0.04}s`;
  });

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = '1';
        entry.target.style.transform = 'translateY(0)';
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1 });

  targets.forEach(el => observer.observe(el));
});
