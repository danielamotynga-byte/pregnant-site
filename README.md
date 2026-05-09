# 🎬 ТРЭШСТРИМ — Полная документация

Профессиональный сайт для каталога трэш-телевидения с AJAX, XML данными и современным дизайном.

## 📁 Структура проекта

```
pregnant-site/
├── index.php                 # Главная страница (точка входа)
├── pregnant16.php, deceived.php, и т.д.  # Страницы шоу
├── api-docs.html            # Документация API
├── sitemap.xml              # Карта сайта для поисковиков
├── robots.txt               # Правила для ботов
├── .htaccess.example        # Конфиг Apache (переименовать в .htaccess)
├── deploy.sh                # Скрипт развёртывания
│
├── ajax/                    # REST API эндпоинты
│   ├── get-shows.php        # Все шоу
│   ├── get-show.php         # Одно шоу
│   ├── search.php           # Поиск
│   ├── stats.php            # Статистика
│   ├── filter.php           # Фильтрация и сортировка
│   ├── trending.php         # Трендовые шоу
│   ├── track-view.php       # Отслеживание просмотров
│   ├── recently-viewed.php  # Недавно просмотренные
│   └── README.md            # Документация AJAX
│
├── db/                      # База данных
│   ├── Database.php         # Класс для работы с БД
│   ├── shows.xml            # Данные о шоу в XML
│   ├── database.sql         # SQL схема (для MySQL)
│   └── (другие файлы БД)
│
├── pages/                   # HTML шаблоны
│   ├── index.html           # Главная
│   ├── pregnant16.html      # Страница шоу
│   └── (остальные страницы)
│
├── css/                     # Стили
│   ├── index.css            # Главная страница
│   ├── theme.css            # Тема и переменные
│   └── (остальные стили)
│
├── js/                      # JavaScript
│   ├── shows-data.js        # Загрузка данных (AJAX)
│   ├── search.js            # Поиск
│   ├── show-carousel.js     # Карусель
│   ├── trending.js          # Трендинг
│   ├── recently-viewed.js   # История просмотров
│   └── (остальные скрипты)
│
└── image/                   # Изображения
    ├── pregnant16.jpg
    ├── deceived.jpg
    └── (остальные)
```

## 🚀 Быстрый старт

### 1. Убедитесь, что XAMPP запущена

```bash
# macOS
/Applications/XAMPP/bin/xampp-start

# Linux
sudo /opt/lampp/bin/lampp start

# Windows
C:\xampp\xampp-start.bat
```

### 2. Откройте сайт в браузере

```
http://localhost/pregnant-site/
```

### 3. Проверьте функциональность

- ✅ Главная страница загружается
- ✅ Карусель работает
- ✅ Поиск функционирует
- ✅ API эндпоинты доступны

## 📡 REST API

**База:** `http://localhost/pregnant-site/ajax/`

### Основные эндпоинты

| Метод | Эндпоинт | Описание |
|-------|----------|---------|
| GET | `/get-shows.php` | Все шоу |
| GET | `/get-show.php?slug=pregnant16` | Одно шоу |
| GET | `/search.php?q=беременна` | Поиск |
| GET | `/stats.php` | Статистика |
| GET | `/filter.php?sort=rating&order=desc` | Фильтрация |
| GET | `/trending.php?limit=5` | Трендинг |
| POST | `/track-view.php` | Отслеживание просмотров |
| GET | `/recently-viewed.php` | Недавно просмотренные |

**Примеры:**

```bash
# Получить все шоу
curl http://localhost/pregnant-site/ajax/get-shows.php

# Трендовые шоу
curl http://localhost/pregnant-site/ajax/trending.php

# Фильтр: рейтинг выше 8.5
curl "http://localhost/pregnant-site/ajax/filter.php?minRating=8.5&sort=rating&order=desc"
```

## 🎨 Особенности

✨ **Современный дизайн** — Тёмная тема, плавные переходы, адаптивность

🔍 **Мощный поиск** — Полнотекстовый поиск по названиям

📊 **Статистика** — Данные о рейтинге, канале, эпизодах

🔥 **Трендинг** — Популярные шоу на основе рейтинга

📺 **История просмотров** — Отслеживание просмотренных шоу

🛡️ **SEO оптимизация** — Sitemap, robots.txt, правильная структура

⚡ **Производительность** — AJAX загрузка, кэширование, сжатие

🔐 **Безопасность** — SQL prepared statements, CSRF защита

## 🎯 Основные функции

### 1. Главная страница
- Карусель с шоу
- Информация о выбранном шоу
- Поисковая строка
- Трендинг
- История просмотров

### 2. Админ панель (`admin.php`)
- Редактирование карточек шоу
- Сохранение в localStorage
- Импорт/экспорт JSON
- Статистика

### 3. AJAX загрузка данных
- Асинхронная загрузка из XML
- Кэширование в localStorage
- Резервная версия при ошибке

### 4. Отслеживание просмотров
- Сохранение в сессии
- Последние 10 просмотров
- Вывод в отдельной секции

## 📊 Данные

Все данные о шоу хранятся в `db/shows.xml`:

```xml
<shows>
  <show>
    <id>1</id>
    <slug>pregnant16</slug>
    <title>Беременна в 16</title>
    <rating>9.4</rating>
    <image>image/pregnant16.jpg</image>
    <description>...</description>
    <link>pregnant16.php</link>
    <channel>Ю</channel>
    <seasons>9</seasons>
    <episodes>300+</episodes>
  </show>
  ...
</shows>
```

## 🔧 Конфигурация

### Подключение к MySQL

Отредактируйте `db/Database.php`:

```php
private $host = 'localhost';
private $user = 'root';
private $password = 'your_password';
private $database = 'treshstream';
```

### Apache .htaccess

1. Переименуйте `.htaccess.example` в `.htaccess`
2. Включит gzip сжатие
3. Добавит кэширование браузера
4. Установит security headers

## 🧪 Тестирование

### Консоль браузера (F12)

```javascript
// Все шоу
fetch('ajax/get-shows.php').then(r => r.json()).then(console.log)

// Поиск
fetch('ajax/search.php?q=беременна').then(r => r.json()).then(console.log)

// Трендинг
fetch('ajax/trending.php').then(r => r.json()).then(console.log)

// Статистика
fetch('ajax/stats.php').then(r => r.json()).then(console.log)
```

### curl в терминале

```bash
# Статистика
curl http://localhost/pregnant-site/ajax/stats.php | jq

# Трендинг
curl http://localhost/pregnant-site/ajax/trending.php?limit=3 | jq
```

## 📱 Адаптивность

- ✅ Desktop (1920px+)
- ✅ Laptop (1280px)
- ✅ Tablet (768px)
- ✅ Mobile (320px)

## 🔐 Безопасность

- Prepared statements для БД
- Санитизация входных данных
- CORS headers где нужны
- Защита от XSS
- X-Frame-Options (clickjacking)
- Content-Type-Options (MIME sniffing)

## 🚀 Production развёртывание

1. **Включить error reporting в production:**

```php
error_reporting(0);
ini_set('display_errors', 0);
```

2. **Включить HTTPS** через SSL сертификат

3. **Настроить кэширование** (Redis, Memcached)

4. **Добавить CDN** для изображений

5. **Мониторить логи** Apache и PHP

## 📞 Контакты и поддержка

**API документация:** [api-docs.html](api-docs.html)
**Карта сайта:** [sitemap.xml](sitemap.xml)
**GitHub:** [repository URL]

---

**Версия:** 1.0  
**Дата:** май 2026  
**Автор:** ТРЭШСТРИМ Team
