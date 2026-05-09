// ================================================
// admin.js — Управление карточками главной
// ================================================

document.addEventListener('DOMContentLoaded', async () => {
  const storageKey = 'trashstream-shows';
  const form = document.querySelector('#show-form');
  const list = document.querySelector('#shows-list');
  const indexInput = document.querySelector('#show-index');
  const titleInput = document.querySelector('#show-title');
  const ratingInput = document.querySelector('#show-rating');
  const linkInput = document.querySelector('#show-link');
  const imageInput = document.querySelector('#show-image');
  const descriptionInput = document.querySelector('#show-description');
  const previewImage = document.querySelector('#preview-image');
  const previewRating = document.querySelector('#preview-rating');
  const previewTitle = document.querySelector('#preview-title');
  const previewDescription = document.querySelector('#preview-description');
  const showsCount = document.querySelector('#shows-count');
  const averageRating = document.querySelector('#average-rating');
  const newButton = document.querySelector('#new-show');
  const resetButton = document.querySelector('#reset-shows');
  const exportButton = document.querySelector('#export-shows');
  
  // Загрузить данные через AJAX
  let shows = [...(await window.getTrashstreamShows())];

  if (!form || !list) return;

  const saveShows = () => {
    localStorage.setItem(storageKey, JSON.stringify(shows));
  };

  const fillForm = (show, index = '') => {
    indexInput.value = index;
    titleInput.value = show?.title || '';
    ratingInput.value = show?.rating || '';
    linkInput.value = show?.link || '';
    imageInput.value = show?.image || '';
    descriptionInput.value = show?.description || '';
    updatePreview();
  };

  const updatePreview = () => {
    previewImage.src = imageInput.value || '../image/pregnant16.jpg';
    previewImage.alt = titleInput.value;
    previewRating.textContent = `Рейтинг ${ratingInput.value || '0.0'}`;
    previewTitle.textContent = titleInput.value || 'Новое шоу';
    previewDescription.textContent = descriptionInput.value || 'Описание появится здесь.';
  };

  const renderStats = () => {
    const total = shows.length;
    const sum = shows.reduce((acc, show) => acc + Number(show.rating || 0), 0);
    const avg = total ? (sum / total).toFixed(1) : '0.0';

    showsCount.textContent = `${total} шоу`;
    averageRating.textContent = `${avg} средний рейтинг`;
  };

  const renderList = () => {
    list.innerHTML = '';
    shows.forEach((show, index) => {
      const row = document.createElement('div');
      const image = document.createElement('img');
      const meta = document.createElement('div');
      const title = document.createElement('strong');
      const details = document.createElement('span');
      const actions = document.createElement('div');
      const edit = document.createElement('button');
      const remove = document.createElement('button');

      row.className = 'admin-row';
      image.src = show.image;
      image.alt = show.title;
      title.textContent = show.title;
      details.textContent = `Рейтинг ${show.rating} • ${show.link}`;
      edit.type = 'button';
      edit.textContent = 'Редактировать';
      edit.addEventListener('click', () => fillForm(show, index));
      remove.type = 'button';
      remove.textContent = 'Удалить';
      remove.addEventListener('click', () => {
        shows.splice(index, 1);
        saveShows();
        renderList();
        fillForm(shows[0] || null, shows.length ? 0 : '');
      });

      meta.append(title, details);
      actions.className = 'admin-row-actions';
      actions.append(edit, remove);
      row.append(image, meta, actions);
      list.appendChild(row);
    });
    renderStats();
  };

  form.addEventListener('input', updatePreview);

  form.addEventListener('submit', (event) => {
    event.preventDefault();
    const show = {
      title: titleInput.value.trim(),
      rating: ratingInput.value.trim(),
      link: linkInput.value.trim(),
      image: imageInput.value.trim(),
      description: descriptionInput.value.trim()
    };
    const index = indexInput.value;

    if (index === '') {
      shows.push(show);
    } else {
      shows[Number(index)] = show;
    }

    saveShows();
    renderList();
    fillForm(show, index === '' ? shows.length - 1 : index);
  });

  newButton.addEventListener('click', () => fillForm(null));

  resetButton.addEventListener('click', () => {
    shows = [...window.TRASHSTREAM_DEFAULT_SHOWS];
    localStorage.removeItem(storageKey);
    renderList();
    fillForm(shows[0], 0);
  });

  exportButton.addEventListener('click', () => {
    navigator.clipboard?.writeText(JSON.stringify(shows, null, 2));
    exportButton.textContent = 'Скопировано';
    setTimeout(() => { exportButton.textContent = 'Экспорт JSON'; }, 1200);
  });

  renderList();
  fillForm(shows[0], 0);
});
