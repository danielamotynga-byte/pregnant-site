// ================================================
// shows-data.js — Загрузка данных шоу через AJAX
// ================================================

window.TRASHSTREAM_DEFAULT_SHOWS = [
  {
    id: 1,
    slug: 'pregnant16',
    title: 'Беременна в 16',
    rating: 9.4,
    link: 'pregnant16.php',
    image: 'image/pregnant16.jpg',
    description: 'Реалити о подростковой беременности: героини сталкиваются с отношениями, семьей, учебой и взрослыми решениями раньше, чем были к этому готовы.',
    channel: 'Ю',
    seasons: '9',
    episodes: '300+'
  },
  {
    id: 2,
    slug: 'deceived',
    title: 'Беременна по обману',
    rating: 8.7,
    link: 'deceived.php',
    image: 'image/deceived.jpg',
    description: 'Истории женщин, которые узнали о беременности после обмана со стороны партнера. В центре выпусков — признания, конфликты, проверки фактов и попытка понять, что делать дальше.',
    channel: 'Ю',
    seasons: '8',
    episodes: '250+'
  },
  {
    id: 3,
    slug: 'muzhskoe',
    title: 'Мужское / Женское',
    rating: 8.9,
    link: 'muzhskoe.php',
    image: 'image/muzhskoe.jpg',
    description: 'Ток-шоу с громкими семейными конфликтами, сложными судьбами и разговорами, где участники пытаются доказать свою правду перед студией.',
    channel: 'ТВ-3',
    seasons: '15',
    episodes: '600+'
  },
  {
    id: 4,
    slug: 'khata',
    title: 'Хата на тата',
    rating: 9.1,
    link: 'khata.php',
    image: 'image/khata.jpeg',
    description: 'Семейное реалити, где папа остается один на хозяйстве и проверяет, насколько он готов справляться с домом, детьми и ежедневными заботами.',
    channel: '1+1',
    seasons: '7',
    episodes: '200+'
  },
  {
    id: 5,
    slug: 'weddings',
    title: '4 свадьбы',
    rating: 8.5,
    link: 'weddings.php',
    image: 'image/weddings.jpeg',
    description: 'Четыре невесты оценивают свадьбы друг друга: платье, банкет, атмосферу и финальное впечатление. Побеждает та, чей праздник оказался сильнее остальных.',
    channel: 'Пятница!',
    seasons: '5',
    episodes: '150+'
  },
  {
    id: 6,
    slug: 'waited',
    title: 'Ждули',
    rating: 7.9,
    link: 'waited.php',
    image: 'image/waited.jpg',
    description: 'Проект о женщинах, которые строят отношения с мужчинами из мест лишения свободы и ждут их возвращения, сталкиваясь с сомнениями, осуждением и надеждой.',
    channel: 'ТВ-3',
    seasons: '4',
    episodes: '120+'
  }
];

// Загрузка данных через AJAX
window.getTrashstreamShows = async () => {
  // Сначала проверяем localStorage
  const saved = localStorage.getItem('trashstream-shows');
  if (saved) {
    try {
      const parsed = JSON.parse(saved);
      if (Array.isArray(parsed) && parsed.length) {
        return parsed;
      }
    } catch (e) {
      console.warn('Ошибка при парсинге localStorage:', e);
    }
  }
  
  // Пытаемся загрузить из API
  try {
    const response = await fetch('ajax/get-shows.php');
    if (!response.ok) throw new Error('API error');
    
    const data = await response.json();
    if (data.success && data.shows) {
      // Сохраняем в localStorage
      localStorage.setItem('trashstream-shows', JSON.stringify(data.shows));
      return data.shows;
    }
  } catch (error) {
    console.warn('Ошибка при загрузке из API:', error);
  }
  
  // Резервная версия — возвращаем стандартные данные
  return window.TRASHSTREAM_DEFAULT_SHOWS;
};
