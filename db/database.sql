-- ТРЭШСТРИМ Database Setup
-- Создание базы данных и всех таблиц

-- Создаем базу данных
CREATE DATABASE IF NOT EXISTS treshstream;
USE treshstream;

-- ================== ТАБЛИЦА ПОЛЬЗОВАТЕЛЕЙ ==================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(40) NOT NULL UNIQUE,
    email VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ================== ТАБЛИЦА ИЗБРАННОГО ==================
CREATE TABLE IF NOT EXISTS favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(64) NOT NULL,
    show_slug VARCHAR(64) NOT NULL,
    show_title VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_favorite (user_id, show_slug),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ================== ТАБЛИЦА ИСТОРИИ ПРОСМОТРОВ ==================
CREATE TABLE IF NOT EXISTS viewing_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(64) NOT NULL,
    show_slug VARCHAR(64) NOT NULL,
    show_title VARCHAR(255) NOT NULL,
    episode_title VARCHAR(255),
    watched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_show (show_slug),
    INDEX idx_date (watched_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ================== ТАБЛИЦА РЕЙТИНГОВ ==================
CREATE TABLE IF NOT EXISTS ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(64) NOT NULL,
    show_slug VARCHAR(64) NOT NULL,
    rating TINYINT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_rating (user_id, show_slug),
    INDEX idx_show (show_slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ================== ТАБЛИЦА ИСТОРИИ ПОИСКА ==================
CREATE TABLE IF NOT EXISTS search_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(64) NOT NULL,
    search_query VARCHAR(255) NOT NULL,
    searched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_query (search_query(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ================== ТАБЛИЦА КОММЕНТАРИЕВ ==================
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(64) NOT NULL,
    show_slug VARCHAR(64) NOT NULL,
    comment_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_show (show_slug),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ================== ТАБЛИЦА НАСТРОЕК ПОЛЬЗОВАТЕЛЯ ==================
CREATE TABLE IF NOT EXISTS user_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(64) NOT NULL UNIQUE,
    theme VARCHAR(16) DEFAULT 'dark',
    notifications BOOLEAN DEFAULT TRUE,
    autoplay BOOLEAN DEFAULT TRUE,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ================== ПРИМЕРЫ ДАННЫХ ==================
-- Добавляем тестовые данные для демонстрации

INSERT INTO favorites (user_id, show_slug, show_title) VALUES 
('demo_user', 'pregnant16', 'Беременна в 16'),
('demo_user', 'deceived', 'Беременна по обману'),
('demo_user', 'khata', 'Хата на тата');

INSERT INTO viewing_history (user_id, show_slug, show_title, episode_title) VALUES 
('demo_user', 'pregnant16', 'Беременна в 16', 'История Кэтлин'),
('demo_user', 'deceived', 'Беременна по обману', '2 выпуск'),
('demo_user', 'khata', 'Хата на тата', '1 выпуск');

INSERT INTO ratings (user_id, show_slug, rating) VALUES 
('demo_user', 'pregnant16', 5),
('demo_user', 'deceived', 4),
('demo_user', 'khata', 5);

INSERT INTO comments (user_id, show_slug, comment_text) VALUES 
('demo_user', 'pregnant16', 'Очень трогательная история!'),
('demo_user', 'deceived', 'Шокирующие истории'),
('demo_user', 'khata', 'Смешная передача');

-- ================== ПРОЦЕДУРЫ ==================

DELIMITER //

-- Получить топ шоу по просмотрам
CREATE PROCEDURE IF NOT EXISTS GetTopShows()
BEGIN
    SELECT 
        show_slug,
        show_title,
        COUNT(*) as view_count,
        AVG(rating) as avg_rating
    FROM viewing_history 
    LEFT JOIN ratings ON viewing_history.show_slug = ratings.show_slug
    GROUP BY show_slug 
    ORDER BY view_count DESC 
    LIMIT 10;
END //

-- Получить статистику пользователя
CREATE PROCEDURE IF NOT EXISTS GetUserStats(IN p_user_id VARCHAR(64))
BEGIN
    SELECT 
        (SELECT COUNT(*) FROM favorites WHERE user_id = p_user_id) as favorites_count,
        (SELECT COUNT(*) FROM viewing_history WHERE user_id = p_user_id) as views_count,
        (SELECT COUNT(*) FROM ratings WHERE user_id = p_user_id) as ratings_count,
        (SELECT COUNT(*) FROM comments WHERE user_id = p_user_id) as comments_count;
END //

DELIMITER ;

-- Вывод сообщения об успехе
SELECT 'База данных treshstream успешно создана!' as message;
