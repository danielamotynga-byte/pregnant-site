<?php
/**
 * ТРЭШСТРИМ - Главная страница
 * Основной точка входа в приложение
 */

// Вывод ошибок для отладки
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Начало сессии
session_start();

// Путь к классам и конфигурации
require_once __DIR__ . '/db/Database.php';

// Инициализация базы данных
try {
    $db = new Database();
} catch (Exception $e) {
    $db = null;
}

// Получение информации о пользователе из сессии или создание нового
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 'user_' . uniqid();
}

$userId = $_SESSION['user_id'];

// Получение избранного пользователя
$favorites = [];
if ($db) {
    try {
        $favorites = $db->getFavorites($userId);
    } catch (Exception $e) {
        $favorites = [];
    }
}

// Подключение HTML-контента главной страницы
include __DIR__ . '/pages/index.html';
?>
