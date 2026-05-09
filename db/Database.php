<?php
/**
 * Database Class - ТРЭШСТРИМ
 * Основной класс для работы с базой данных
 */

class Database {
    private $host = 'localhost';
    private $user = 'root';
    private $password = '';
    private $database = 'treshstream';
    private $conn = null;
    
    public function __construct() {
        $this->connect();
        $this->ensureTables();
    }
    
    private function connect() {
        $this->conn = new mysqli($this->host, $this->user, $this->password);
        
        if ($this->conn->connect_error) {
            throw new Exception("Ошибка подключения к MySQL: " . $this->conn->connect_error);
        }
        
        $this->conn->query("CREATE DATABASE IF NOT EXISTS {$this->database} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        if (!$this->conn->select_db($this->database)) {
            throw new Exception("Ошибка выбора базы данных: " . $this->conn->error);
        }
        
        $this->conn->set_charset("utf8mb4");
    }

    private function ensureTables() {
        if (!$this->conn->query("
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(40) NOT NULL UNIQUE,
                email VARCHAR(120) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ")) {
            throw new Exception("Ошибка создания таблицы пользователей: " . $this->conn->error);
        }

        $this->conn->query("
            CREATE TABLE IF NOT EXISTS favorites (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id VARCHAR(64) NOT NULL,
                show_slug VARCHAR(64) NOT NULL,
                show_title VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_favorite (user_id, show_slug),
                INDEX idx_user (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $this->conn->query("
            CREATE TABLE IF NOT EXISTS comments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id VARCHAR(64) NOT NULL,
                show_slug VARCHAR(64) NOT NULL,
                comment_text TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_show (show_slug),
                INDEX idx_user (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    // ================== ПОЛЬЗОВАТЕЛИ ==================

    public function registerUser($username, $email, $password) {
        $username = trim($username);
        $email = trim(strtolower($email));

        if (!preg_match('/^[a-zA-Z0-9_а-яА-ЯёЁ-]{3,40}$/u', $username)) {
            throw new Exception('Логин должен быть от 3 до 40 символов.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Введите правильный email.');
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("
            INSERT INTO users (username, email, password_hash)
            VALUES (?, ?, ?)
        ");
        if (!$stmt) {
            throw new Exception('Не удалось подготовить регистрацию: ' . $this->conn->error);
        }
        $stmt->bind_param("sss", $username, $email, $passwordHash);

        if (!$stmt->execute()) {
            if ($this->conn->errno === 1062) {
                throw new Exception('Такой логин или email уже зарегистрирован.');
            }
            throw new Exception('Не удалось создать пользователя.');
        }

        return [
            'id' => $this->conn->insert_id,
            'username' => $username,
            'email' => $email
        ];
    }

    public function loginUser($login, $password) {
        $login = trim($login);
        $stmt = $this->conn->prepare("
            SELECT id, username, email, password_hash
            FROM users
            WHERE username = ? OR email = ?
            LIMIT 1
        ");
        if (!$stmt) {
            throw new Exception('Не удалось подготовить вход: ' . $this->conn->error);
        }
        $stmt->bind_param("ss", $login, $login);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return null;
        }

        return $user;
    }
    
    // ================== ИЗБРАННОЕ ==================
    
    public function addFavorite($userId, $showSlug, $showTitle) {
        $stmt = $this->conn->prepare("
            INSERT INTO favorites (user_id, show_slug, show_title) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE show_title = VALUES(show_title)
        ");
        $stmt->bind_param("sss", $userId, $showSlug, $showTitle);
        return $stmt->execute();
    }
    
    public function removeFavorite($userId, $showSlug) {
        $stmt = $this->conn->prepare("DELETE FROM favorites WHERE user_id = ? AND show_slug = ?");
        $stmt->bind_param("ss", $userId, $showSlug);
        return $stmt->execute();
    }
    
    public function getFavorites($userId) {
        $stmt = $this->conn->prepare("SELECT * FROM favorites WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function isFavorite($userId, $showSlug) {
        $stmt = $this->conn->prepare("SELECT id FROM favorites WHERE user_id = ? AND show_slug = ?");
        $stmt->bind_param("ss", $userId, $showSlug);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }
    
    // ================== ИСТОРИЯ ПРОСМОТРОВ ==================
    
    public function addToHistory($userId, $showSlug, $showTitle, $episodeTitle = null) {
        $stmt = $this->conn->prepare("
            INSERT INTO viewing_history (user_id, show_slug, show_title, episode_title) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("ssss", $userId, $showSlug, $showTitle, $episodeTitle);
        return $stmt->execute();
    }
    
    public function getHistory($userId, $limit = 20) {
        $stmt = $this->conn->prepare("
            SELECT * FROM viewing_history 
            WHERE user_id = ? 
            ORDER BY watched_at DESC 
            LIMIT ?
        ");
        $stmt->bind_param("si", $userId, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function clearHistory($userId) {
        $stmt = $this->conn->prepare("DELETE FROM viewing_history WHERE user_id = ?");
        $stmt->bind_param("s", $userId);
        return $stmt->execute();
    }
    
    // ================== РЕЙТИНГ ==================
    
    public function setRating($userId, $showSlug, $rating) {
        $stmt = $this->conn->prepare("
            INSERT INTO ratings (user_id, show_slug, rating) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE rating = VALUES(rating)
        ");
        $stmt->bind_param("ssi", $userId, $showSlug, $rating);
        return $stmt->execute();
    }
    
    public function getUserRating($userId, $showSlug) {
        $stmt = $this->conn->prepare("SELECT rating FROM ratings WHERE user_id = ? AND show_slug = ?");
        $stmt->bind_param("ss", $userId, $showSlug);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result ? $result['rating'] : null;
    }
    
    public function getShowAverageRating($showSlug) {
        $stmt = $this->conn->prepare("SELECT AVG(rating) as avg, COUNT(*) as count FROM ratings WHERE show_slug = ?");
        $stmt->bind_param("s", $showSlug);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    // ================== ПОИСК ==================
    
    public function addSearchQuery($userId, $query) {
        $stmt = $this->conn->prepare("INSERT INTO search_history (user_id, search_query) VALUES (?, ?)");
        $stmt->bind_param("ss", $userId, $query);
        return $stmt->execute();
    }
    
    public function getSearchHistory($userId, $limit = 10) {
        $stmt = $this->conn->prepare("
            SELECT DISTINCT search_query, MAX(searched_at) as last 
            FROM search_history 
            WHERE user_id = ? 
            GROUP BY search_query 
            ORDER BY last DESC 
            LIMIT ?
        ");
        $stmt->bind_param("si", $userId, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function clearSearchHistory($userId) {
        $stmt = $this->conn->prepare("DELETE FROM search_history WHERE user_id = ?");
        $stmt->bind_param("s", $userId);
        return $stmt->execute();
    }
    
    // ================== КОММЕНТАРИИ ==================
    
    public function addComment($userId, $showSlug, $comment) {
        $stmt = $this->conn->prepare("INSERT INTO comments (user_id, show_slug, comment_text) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $userId, $showSlug, $comment);
        return $stmt->execute();
    }
    
    public function getComments($showSlug, $limit = 50) {
        $stmt = $this->conn->prepare("
            SELECT comments.*, users.username
            FROM comments
            LEFT JOIN users ON users.id = comments.user_id
            WHERE comments.show_slug = ?
            ORDER BY comments.created_at DESC
            LIMIT ?
        ");
        $stmt->bind_param("si", $showSlug, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    // ================== НАСТРОЙКИ ==================
    
    public function saveSettings($userId, $settings) {
        $theme = $settings['theme'] ?? 'dark';
        $notifications = $settings['notifications'] ?? 1;
        $autoplay = $settings['autoplay'] ?? 1;
        
        $stmt = $this->conn->prepare("
            INSERT INTO user_settings (user_id, theme, notifications, autoplay) 
            VALUES (?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE 
                theme = VALUES(theme),
                notifications = VALUES(notifications),
                autoplay = VALUES(autoplay)
        ");
        $stmt->bind_param("ssii", $userId, $theme, $notifications, $autoplay);
        return $stmt->execute();
    }
    
    public function getSettings($userId) {
        $stmt = $this->conn->prepare("SELECT * FROM user_settings WHERE user_id = ?");
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    // ================== СТАТИСТИКА ==================
    
    public function getStats() {
        $stats = [];
        
        $result = $this->conn->query("SELECT COUNT(*) as total FROM favorites");
        $stats['favorites'] = $result->fetch_assoc()['total'];
        
        $result = $this->conn->query("SELECT COUNT(*) as total FROM viewing_history");
        $stats['views'] = $result->fetch_assoc()['total'];
        
        $result = $this->conn->query("SELECT COUNT(*) as total FROM ratings");
        $stats['ratings'] = $result->fetch_assoc()['total'];
        
        $result = $this->conn->query("SELECT COUNT(*) as total FROM comments");
        $stats['comments'] = $result->fetch_assoc()['total'];
        
        return $stats;
    }
    
    public function getTopShows($limit = 10) {
        return $this->conn->query("
            SELECT show_slug, show_title, COUNT(*) as view_count 
            FROM viewing_history 
            GROUP BY show_slug 
            ORDER BY view_count DESC 
            LIMIT $limit
        ")->fetch_all(MYSQLI_ASSOC);
    }
    
    public function close() {
        $this->conn->close();
    }

    public function getConnection() {
        return $this->conn;
    }
}
