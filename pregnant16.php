<?php
session_start();
require_once __DIR__ . '/db/Database.php';

try {
    $db = new Database();
} catch (Exception $e) {
    $db = null;
}

if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 'user_' . uniqid();
}

$userId = $_SESSION['user_id'];
$favorites = [];
if ($db) {
    try {
        $favorites = $db->getFavorites($userId);
    } catch (Exception $e) {
        $favorites = [];
    }
}

$showSlug = 'pregnant16';
$showTitle = 'Беременна в 16';
include __DIR__ . '/pages/pregnant16.html';
?>
