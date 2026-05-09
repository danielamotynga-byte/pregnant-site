<?php
/**
 * Database Setup - ТРЭШСТРИМ
 * Установка и настройка базы данных для сайта
 */

// Конфигурация подключения к БД
$db_config = [
    'host' => 'localhost',
    'user' => 'root',
    'password' => '',
    'database' => 'treshstream'
];

// Создание подключения
function getDBConnection() {
    $config = [
        'host' => 'localhost',
        'user' => 'root',
        'password' => '',
        'database' => 'treshstream'
    ];
    
    $conn = new mysqli($config['host'], $config['user'], $config['password']);
    
    if ($conn->connect_error) {
        die("Ошибка подключения: " . $conn->connect_error);
    }
    
    return $conn;
}

// Создание всех таблиц
function setupDatabase() {
    $conn = getDBConnection();
    
    // Создаем базу данных если не существует
    $conn->query("CREATE DATABASE IF NOT EXISTS treshstream");
    $conn->select_db('treshstream');

    // Таблица пользователей
    $conn->query("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(40) NOT NULL UNIQUE,
            email VARCHAR(120) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Таблица избранного
    $conn->query("
        CREATE TABLE IF NOT EXISTS favorites (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id VARCHAR(64) NOT NULL,
            show_slug VARCHAR(64) NOT NULL,
            show_title VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_favorite (user_id, show_slug)
        )
    ");
    
    // Таблица истории просмотров
    $conn->query("
        CREATE TABLE IF NOT EXISTS viewing_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id VARCHAR(64) NOT NULL,
            show_slug VARCHAR(64) NOT NULL,
            show_title VARCHAR(255) NOT NULL,
            episode_title VARCHAR(255),
            watched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_show (show_slug)
        )
    ");
    
    // Таблица рейтингов
    $conn->query("
        CREATE TABLE IF NOT EXISTS ratings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id VARCHAR(64) NOT NULL,
            show_slug VARCHAR(64) NOT NULL,
            rating TINYINT NOT NULL CHECK (rating >= 1 AND rating <= 5),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_rating (user_id, show_slug)
        )
    ");
    
    // Таблица истории поиска
    $conn->query("
        CREATE TABLE IF NOT EXISTS search_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id VARCHAR(64) NOT NULL,
            search_query VARCHAR(255) NOT NULL,
            searched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_query (search_query)
        )
    ");
    
    // Таблица комментариев
    $conn->query("
        CREATE TABLE IF NOT EXISTS comments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id VARCHAR(64) NOT NULL,
            show_slug VARCHAR(64) NOT NULL,
            comment_text TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_show (show_slug)
        )
    ");
    
    // Таблица пользовательских настроек
    $conn->query("
        CREATE TABLE IF NOT EXISTS user_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id VARCHAR(64) NOT NULL UNIQUE,
            theme VARCHAR(16) DEFAULT 'dark',
            notifications BOOLEAN DEFAULT TRUE,
            autoplay BOOLEAN DEFAULT TRUE,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    
    echo "База данных успешно настроена!";
    
    $conn->close();
}

// Запускаем установку при прямом访问
if (php_sapi_name() === 'cli' || isset($_GET['setup'])) {
    setupDatabase();
}
