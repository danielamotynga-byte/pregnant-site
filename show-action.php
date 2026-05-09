<?php
session_start();
require_once __DIR__ . '/db/Database.php';

$redirect = $_SERVER['HTTP_REFERER'] ?? 'index.php';
$action = $_POST['action'] ?? '';
$showSlug = $_POST['show_slug'] ?? '';
$showTitle = $_POST['show_title'] ?? '';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    $_SESSION['auth_error'] = 'Сначала войдите в аккаунт.';
    header('Location: login.php');
    exit;
}

try {
    $db = new Database();
    $userId = (string)$_SESSION['user_id'];

    if ($action === 'add_favorite' && $showSlug && $showTitle) {
        $db->addFavorite($userId, $showSlug, $showTitle);
        $_SESSION['auth_message'] = 'Шоу добавлено в любимые.';
    } elseif ($action === 'remove_favorite' && $showSlug) {
        $db->removeFavorite($userId, $showSlug);
        $_SESSION['auth_message'] = 'Шоу убрано из любимых.';
    } elseif ($action === 'add_comment' && $showSlug) {
        $comment = trim($_POST['comment'] ?? '');
        if ($comment === '') {
            throw new Exception('Комментарий не может быть пустым.');
        }
        $db->addComment($userId, $showSlug, $comment);
        $_SESSION['auth_message'] = 'Комментарий добавлен.';
    }
} catch (Exception $e) {
    $_SESSION['auth_error'] = $e->getMessage();
}

header('Location: ' . $redirect);
exit;
