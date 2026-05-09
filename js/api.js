/**
 * API Client - ТРЭШСТРИМ
 * JavaScript клиент для работы с базой данных
 */

class TreshStreamAPI {
    constructor() {
        this.apiUrl = '../db/api.php';
        this.userId = this.getUserId();
    }
    
    // Генерация уникального ID пользователя
    getUserId() {
        let userId = localStorage.getItem('treshstream_user_id');
        if (!userId) {
            userId = 'user_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            localStorage.setItem('treshstream_user_id', userId);
        }
        return userId;
    }
    
    // Универсальный запрос к API
    async request(action, data = {}) {
        const formData = new FormData();
        formData.append('action', action);
        formData.append('user_id', this.userId);
        
        for (const [key, value] of Object.entries(data)) {
            formData.append(key, value);
        }
        
        try {
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                body: formData
            });
            return await response.json();
        } catch (error) {
            console.error('API Error:', error);
            return { success: false, error: error.message };
        }
    }
    
    // ================== ИЗБРАННОЕ ==================
    
    async addFavorite(showSlug, showTitle) {
        return await this.request('add_favorite', { show_slug: showSlug, show_title: showTitle });
    }
    
    async removeFavorite(showSlug) {
        return await this.request('remove_favorite', { show_slug: showSlug });
    }
    
    async getFavorites() {
        return await this.request('get_favorites');
    }
    
    async isFavorite(showSlug) {
        return await this.request('is_favorite', { show_slug: showSlug });
    }
    
    // ================== ИСТОРИЯ ПРОСМОТРОВ ==================
    
    async addToHistory(showSlug, showTitle, episodeTitle = null) {
        return await this.request('add_history', { 
            show_slug: showSlug, 
            show_title: showTitle,
            episode_title: episodeTitle 
        });
    }
    
    async getHistory(limit = 20) {
        return await this.request('get_history', { limit });
    }
    
    async clearHistory() {
        return await this.request('clear_history');
    }
    
    // ================== РЕЙТИНГ ==================
    
    async setRating(showSlug, rating) {
        return await this.request('set_rating', { show_slug: showSlug, rating });
    }
    
    async getUserRating(showSlug) {
        return await this.request('get_user_rating', { show_slug: showSlug });
    }
    
    async getShowRating(showSlug) {
        return await this.request('get_show_rating', { show_slug: showSlug });
    }
    
    // ================== ПОИСК ==================
    
    async addSearch(query) {
        return await this.request('add_search', { query });
    }
    
    async getSearchHistory(limit = 10) {
        return await this.request('get_search_history', { limit });
    }
    
    async clearSearchHistory() {
        return await this.request('clear_search_history');
    }
    
    // ================== КОММЕНТАРИИ ==================
    
    async addComment(showSlug, comment) {
        return await this.request('add_comment', { show_slug: showSlug, comment });
    }
    
    async getComments(showSlug, limit = 50) {
        return await this.request('get_comments', { show_slug: showSlug, limit });
    }
    
    // ================== НАСТРОЙКИ ==================
    
    async saveSettings(settings) {
        return await this.request('save_settings', settings);
    }
    
    async getSettings() {
        return await this.request('get_settings');
    }
    
    // ================== СТАТИСТИКА ==================
    
    async getStats() {
        return await this.request('get_stats');
    }
    
    async getTopShows(limit = 10) {
        return await this.request('get_top_shows', { limit });
    }
}

// Глобальный экземпляр API
const api = new TreshStreamAPI();

// ================== UI КОМПОНЕНТЫ ==================

// Рендер звезд рейтинга
function renderStars(rating, interactive = false, showSlug = '') {
    let html = '<div class="rating-stars' + (interactive ? ' interactive' : '') + '">';
    for (let i = 1; i <= 5; i++) {
        const active = i <= rating ? 'active' : '';
        const clickAttr = interactive ? `onclick="api.setRating('${showSlug}', ${i})"` : '';
        html += `<span class="star ${active}" ${clickAttr}>★</span>`;
    }
    html += '</div>';
    return html;
}

// Рендер кнопки избранного
function renderFavoriteButton(showSlug, showTitle) {
    return `<button class="favorite-btn" onclick="toggleFavorite('${showSlug}', '${showTitle}')">
        <span class="heart-icon">♡</span>
        <span class="btn-text">В избранное</span>
    </button>`;
}

// Переключение избранного
async function toggleFavorite(showSlug, showTitle) {
    const isFav = await api.isFavorite(showSlug);
    if (isFav) {
        await api.removeFavorite(showSlug);
    } else {
        await api.addFavorite(showSlug, showTitle);
    }
    updateFavoriteButton(showSlug);
}

// Обновление кнопки избранного
async function updateFavoriteButton(showSlug) {
    const btn = document.querySelector('.favorite-btn');
    if (!btn) return;
    
    const isFav = await api.isFavorite(showSlug);
    const heart = btn.querySelector('.heart-icon');
    const text = btn.querySelector('.btn-text');
    
    if (isFav) {
        heart.textContent = '♥';
        text.textContent = 'В избранном';
        btn.classList.add('active');
    } else {
        heart.textContent = '♡';
        text.textContent = 'В избранное';
        btn.classList.remove('active');
    }
}

// Показать историю просмотров
async function showHistoryModal() {
    const history = await api.getHistory(10);
    
    let html = '<div class="modal-content">';
    html += '<h2>История просмотров</h2>';
    
    if (history.length === 0) {
        html += '<p>Вы ещё ничего не смотрели</p>';
    } else {
        html += '<ul class="history-list">';
        for (const item of history) {
            html += `<li>
                <a href="${item.show_slug}.html">${item.show_title}</a>
                <span class="date">${new Date(item.watched_at).toLocaleDateString()}</span>
            </li>`;
        }
        html += '</ul>';
    }
    
    html += '<button onclick="api.clearHistory()">Очистить историю</button>';
    html += '</div>';
    
    // Показать модалку
    showModal(html);
}

// Модальное окно
function showModal(content) {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.innerHTML = `<div class="modal">${content}</div><button class="modal-close" onclick="this.closest('.modal-overlay').remove()">✕</button>`;
    document.body.appendChild(modal);
}

// ================== АНИМАЦИИ ==================

// Анимация появления элементов
function animateOnScroll() {
    const elements = document.querySelectorAll('.show-card, .episode-item, .history-item');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, { threshold: 0.1 });
    
    elements.forEach(el => observer.observe(el));
}

// Анимация счетчиков
function animateCounters() {
    const counters = document.querySelectorAll('.counter');
    
    counters.forEach(counter => {
        const target = parseInt(counter.dataset.target);
        const duration = 2000;
        const step = target / (duration / 16);
        let current = 0;
        
        const timer = setInterval(() => {
            current += step;
            if (current >= target) {
                counter.textContent = target;
                clearInterval(timer);
            } else {
                counter.textContent = Math.floor(current);
            }
        }, 16);
    });
}

// Инициализация при загрузке
document.addEventListener('DOMContentLoaded', () => {
    animateOnScroll();
    
    // Добавляем стили для анимаций
    const style = document.createElement('style');
    style.textContent = `
        .animate-in {
            animation: fadeInUp 0.6s ease forwards;
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .rating-stars {
            display: inline-flex;
            gap: 4px;
        }
        .rating-stars .star {
            font-size: 24px;
            color: #444;
            cursor: default;
            transition: all 0.2s;
        }
        .rating-stars.interactive .star {
            cursor: pointer;
        }
        .rating-stars .star.active {
            color: #ffcc00;
        }
        .rating-stars.interactive .star:hover {
            transform: scale(1.2);
        }
        .favorite-btn {
            background: #222;
            border: 1px solid #333;
            color: #fff;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .favorite-btn:hover {
            background: #333;
            border-color: #ff2d55;
        }
        .favorite-btn.active {
            background: #ff2d55;
            border-color: #ff2d55;
        }
        .favorite-btn .heart-icon {
            font-size: 18px;
        }
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        .modal {
            background: #141414;
            padding: 32px;
            border-radius: 12px;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        .modal-close {
            position: absolute;
            top: 16px;
            right: 16px;
            background: none;
            border: none;
            color: #fff;
            font-size: 24px;
            cursor: pointer;
        }
        .history-list {
            list-style: none;
            padding: 0;
        }
        .history-list li {
            padding: 12px 0;
            border-bottom: 1px solid #222;
            display: flex;
            justify-content: space-between;
        }
        .history-list .date {
            color: #777;
            font-size: 12px;
        }
    `;
    document.head.appendChild(style);
});