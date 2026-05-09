<?php
/**
 * API Endpoint - ТРЭШСТРИМ
 * Обработка всех AJAX запросов к базе данных
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Предотвращаем кэширование
header('Cache-Control: no-cache, no-store, must-revalidate');

// Обработка preflight запросов
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/Database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

// Получение данных
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$userId = $_POST['user_id'] ?? $_GET['user_id'] ?? '';

// Если нет user_id - используем IP
if (empty($userId)) {
    $userId = 'guest_' . md5($_SERVER['REMOTE_ADDR']);
}

$response = ['success' => false];

switch ($action) {
    // ================== ИЗБРАННОЕ ==================
    
    case 'add_favorite':
        $showSlug = $_POST['show_slug'] ?? '';
        $showTitle = $_POST['show_title'] ?? '';
        
        if ($showSlug && $showTitle) {
            $stmt = $conn->prepare("
                INSERT INTO favorites (user_id, show_slug, show_title) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE show_title = VALUES(show_title)
            ");
            $stmt->bind_param("sss", $userId, $showSlug, $showTitle);
            $response['success'] = $stmt->execute();
        }
        break;
        
    case 'remove_favorite':
        $showSlug = $_POST['show_slug'] ?? '';
        
        if ($showSlug) {
            $stmt = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND show_slug = ?");
            $stmt->bind_param("ss", $userId, $showSlug);
            $response['success'] = $stmt->execute();
        }
        break;
        
    case 'get_favorites':
        $stmt = $conn->prepare("SELECT * FROM favorites WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $response['favorites'] = $result->fetch_all(MYSQLI_ASSOC);
        $response['success'] = true;
        break;
        
    case 'is_favorite':
        $showSlug = $_POST['show_slug'] ?? '';
        
        $stmt = $conn->prepare("SELECT id FROM favorites WHERE user_id = ? AND show_slug = ?");
        $stmt->bind_param("ss", $userId, $showSlug);
        $stmt->execute();
        $response['is_favorite'] = $stmt->get_result()->num_rows > 0;
        $response['success'] = true;
        break;
    
    // ================== ИСТОРИЯ ПРОСМОТРОВ ==================
    
    case 'add_history':
        $showSlug = $_POST['show_slug'] ?? '';
        $showTitle = $_POST['show_title'] ?? '';
        $episodeTitle = $_POST['episode_title'] ?? null;
        
        if ($showSlug && $showTitle) {
            $stmt = $conn->prepare("
                INSERT INTO viewing_history (user_id, show_slug, show_title, episode_title) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param("ssss", $userId, $showSlug, $showTitle, $episodeTitle);
            $response['success'] = $stmt->execute();
        }
        break;
        
    case 'get_history':
        $limit = (int)($_POST['limit'] ?? 20);
        
        $stmt = $conn->prepare("
            SELECT * FROM viewing_history 
            WHERE user_id = ? 
            ORDER BY watched_at DESC 
            LIMIT ?
        ");
        $stmt->bind_param("si", $userId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $response['history'] = $result->fetch_all(MYSQLI_ASSOC);
        $response['success'] = true;
        break;
        
    case 'clear_history':
        $stmt = $conn->prepare("DELETE FROM viewing_history WHERE user_id = ?");
        $stmt->bind_param("s", $userId);
        $response['success'] = $stmt->execute();
        break;
    
    // ================== РЕЙТИНГ ==================
    
    case 'set_rating':
        $showSlug = $_POST['show_slug'] ?? '';
        $rating = (int)($_POST['rating'] ?? 0);
        
        if ($showSlug && $rating >= 1 && $rating <= 5) {
            $stmt = $conn->prepare("
                INSERT INTO ratings (user_id, show_slug, rating) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE rating = VALUES(rating)
            ");
            $stmt->bind_param("ssi", $userId, $showSlug, $rating);
            $response['success'] = $stmt->execute();
        }
        break;
        
    case 'get_user_rating':
        $showSlug = $_POST['show_slug'] ?? '';
        
        $stmt = $conn->prepare("SELECT rating FROM ratings WHERE user_id = ? AND show_slug = ?");
        $stmt->bind_param("ss", $userId, $showSlug);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $response['rating'] = $result ? $result['rating'] : null;
        $response['success'] = true;
        break;
        
    case 'get_show_rating':
        $showSlug = $_POST['show_slug'] ?? '';
        
        $stmt = $conn->prepare("SELECT AVG(rating) as avg, COUNT(*) as count FROM ratings WHERE show_slug = ?");
        $stmt->bind_param("s", $showSlug);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $response['average'] = $result['avg'] ? round($result['avg'], 1) : null;
        $response['count'] = (int)$result['count'];
        $response['success'] = true;
        break;
    
    // ================== ПОИСК ==================
    
    case 'add_search':
        $query = $_POST['query'] ?? '';
        
        if ($query) {
            $stmt = $conn->prepare("INSERT INTO search_history (user_id, search_query) VALUES (?, ?)");
            $stmt->bind_param("ss", $userId, $query);
            $response['success'] = $stmt->execute();
        }
        break;
        
    case 'get_search_history':
        $limit = (int)($_POST['limit'] ?? 10);
        
        $stmt = $conn->prepare("
            SELECT DISTINCT search_query, MAX(searched_at) as last 
            FROM search_history 
            WHERE user_id = ? 
            GROUP BY search_query 
            ORDER BY last DESC 
            LIMIT ?
        ");
        $stmt->bind_param("si", $userId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $response['history'] = $result->fetch_all(MYSQLI_ASSOC);
        $response['success'] = true;
        break;
        
    case 'clear_search_history':
        $stmt = $conn->prepare("DELETE FROM search_history WHERE user_id = ?");
        $stmt->bind_param("s", $userId);
        $response['success'] = $stmt->execute();
        break;
    
    // ================== КОММЕНТАРИИ ==================
    
    case 'add_comment':
        $showSlug = $_POST['show_slug'] ?? '';
        $comment = $_POST['comment'] ?? '';
        
        if ($showSlug && $comment) {
            $stmt = $conn->prepare("INSERT INTO comments (user_id, show_slug, comment_text) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $userId, $showSlug, $comment);
            $response['success'] = $stmt->execute();
        }
        break;
        
    case 'get_comments':
        $showSlug = $_POST['show_slug'] ?? '';
        $limit = (int)($_POST['limit'] ?? 50);
        
        $stmt = $conn->prepare("
            SELECT * FROM comments 
            WHERE show_slug = ? 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->bind_param("si", $showSlug, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $response['comments'] = $result->fetch_all(MYSQLI_ASSOC);
        $response['success'] = true;
        break;
    
    // ================== НАСТРОЙКИ ==================
    
    case 'save_settings':
        $theme = $_POST['theme'] ?? 'dark';
        $notifications = isset($_POST['notifications']) ? (int)$_POST['notifications'] : 1;
        $autoplay = isset($_POST['autoplay']) ? (int)$_POST['autoplay'] : 1;
        
        $stmt = $conn->prepare("
            INSERT INTO user_settings (user_id, theme, notifications, autoplay) 
            VALUES (?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE 
                theme = VALUES(theme),
                notifications = VALUES(notifications),
                autoplay = VALUES(autoplay)
        ");
        $stmt->bind_param("ssii", $userId, $theme, $notifications, $autoplay);
        $response['success'] = $stmt->execute();
        break;
        
    case 'get_settings':
        $stmt = $conn->prepare("SELECT * FROM user_settings WHERE user_id = ?");
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $response['settings'] = $result ?: ['theme' => 'dark', 'notifications' => 1, 'autoplay' => 1];
        $response['success'] = true;
        break;
    
    // ================== СТАТИСТИКА ==================
    
    case 'get_stats':
        $stats = [];
        
        $result = $conn->query("SELECT COUNT(*) as total FROM favorites");
        $stats['favorites'] = (int)$result->fetch_assoc()['total'];
        
        $result = $conn->query("SELECT COUNT(*) as total FROM viewing_history");
        $stats['views'] = (int)$result->fetch_assoc()['total'];
        
        $result = $conn->query("SELECT COUNT(*) as total FROM ratings");
        $stats['ratings'] = (int)$result->fetch_assoc()['total'];
        
        $result = $conn->query("SELECT COUNT(*) as total FROM comments");
        $stats['comments'] = (int)$result->fetch_assoc()['total'];
        
        $response['stats'] = $stats;
        $response['success'] = true;
        break;
        
    case 'get_top_shows':
        $limit = (int)($_POST['limit'] ?? 10);
        
        $result = $conn->query("
            SELECT show_slug, show_title, COUNT(*) as view_count 
            FROM viewing_history 
            GROUP BY show_slug 
            ORDER BY view_count DESC 
            LIMIT $limit
        ");
        $response['top_shows'] = $result->fetch_all(MYSQLI_ASSOC);
        $response['success'] = true;
        break;
    
    // ================== ПО УМОЛЧАНИЮ ==================
    
    default:
        $response['error'] = 'Unknown action';
        break;
}

$conn->close();

echo json_encode($response);
