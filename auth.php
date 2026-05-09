<?php
session_start();
require_once __DIR__ . '/db/Database.php';

$action = $_POST['action'] ?? '';
$redirect = $_SERVER['HTTP_REFERER'] ?? 'index.php';
$host = $_SERVER['HTTP_HOST'] ?? '';

if ($host && parse_url($redirect, PHP_URL_HOST) && parse_url($redirect, PHP_URL_HOST) !== $host) {
    $redirect = 'index.php';
}

try {
    $db = new Database();

    if ($action === 'register') {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $email === '' || strlen($password) < 6) {
            throw new Exception('Заполните логин, email и пароль от 6 символов.');
        }

        $user = $db->registerUser($username, $email, $password);
        $_SESSION['user_id'] = (string)$user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['auth_message'] = 'Регистрация прошла успешно.';
    } elseif ($action === 'login') {
        $login = trim($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';
        $user = $db->loginUser($login, $password);

        if (!$user) {
            throw new Exception('Неверный логин/email или пароль.');
        }

        $_SESSION['user_id'] = (string)$user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['auth_message'] = 'Вы вошли в аккаунт.';
    } elseif ($action === 'logout') {
        unset($_SESSION['user_id'], $_SESSION['username']);
        $_SESSION['auth_message'] = 'Вы вышли из аккаунта.';
    }
} catch (Exception $e) {
    $_SESSION['auth_error'] = $e->getMessage();
}

header('Location: ' . $redirect);
exit;
