// ================================================
// show-carousel.js — Выбор шоу на главной
// ================================================

document.addEventListener('DOMContentLoaded', async () => {
  const carousel = document.querySelector('.show-carousel');
  const prevButton = document.querySelector('.carousel-control-prev');
  const nextButton = document.querySelector('.carousel-control-next');
  const heroHeader = document.querySelector('.hero-header');
  const heroTitle = document.querySelector('#hero-title');
  const heroDescription = document.querySelector('#hero-description');
  const heroLink = document.querySelector('#hero-link');
  const title = document.querySelector('#show-info-title');
  const description = document.querySelector('#show-info-description');
  const rating = document.querySelector('#show-info-rating');
  const link = document.querySelector('#show-info-link');
  let activeIndex = 0;

  if (!carousel || !title || !description || !link) return;

  // Загрузить данные через AJAX
  const shows = await window.getTrashstreamShows();
  if (shows.length) {
    carousel.innerHTML = '';
    shows.forEach((show, index) => {
      const card = document.createElement('button');
      const image = document.createElement('img');
      const label = document.createElement('span');

      card.type = 'button';
      card.className = `show-card${index === 0 ? ' is-active' : ''}`;
      card.dataset.title = show.title;
      card.dataset.rating = show.rating;
      card.dataset.link = show.link;
      card.dataset.image = show.image;
      card.dataset.description = show.description;

      image.src = show.image;
      image.alt = show.title;
      image.onerror = function() {
        // Если изображение не загружается, показать плейсхолдер
        this.style.backgroundColor = '#ddd';
        this.alt = 'Изображение не найдено';
      };
      label.textContent = show.title;

      card.append(image, label);
      carousel.appendChild(card);
    });
  }

  const cards = document.querySelectorAll('.show-card');
  if (!cards.length) return;

  const selectCard = (index) => {
    activeIndex = Math.max(0, Math.min(index, cards.length - 1));
    const card = cards[activeIndex];

    cards.forEach((item) => item.classList.remove('is-active'));
    card.classList.add('is-active');

    title.textContent = card.dataset.title;
    description.textContent = card.dataset.description;
    if (heroTitle) heroTitle.textContent = card.dataset.title;
    if (rating) rating.textContent = `Рейтинг ${card.dataset.rating}`;
    if (heroDescription) heroDescription.textContent = card.dataset.description;
    if (heroLink) heroLink.href = card.dataset.link;
    link.href = card.dataset.link;
    if (card.dataset.image && heroHeader) {
      heroHeader.style.backgroundImage = `url('${card.dataset.image}')`;
    }
    card.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
  };

  cards.forEach((card, index) => {
    card.addEventListener('click', () => {
      selectCard(index);
      window.location.href = card.dataset.link;
    });

    card.addEventListener('mouseenter', () => {
      selectCard(index);
    });
  });

  prevButton?.addEventListener('click', () => {
    selectCard(activeIndex - 1);
  });

  nextButton?.addEventListener('click', () => {
    selectCard(activeIndex + 1);
  });

  selectCard(0);
});
