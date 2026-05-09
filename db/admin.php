<?php
/**
 * Admin Panel - ТРЭШСТРИМ
 * Панель управления базой данных
 */

$host = 'localhost';
$user = 'root';
$password = '';
$database = 'treshstream';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

$conn->set_charset('utf8mb4');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ТРЭШСТРИМ Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #0a0a0a;
            color: #f0f0f0;
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
        }
        .header {
            background: linear-gradient(135deg, #ff2d55, #ff9500);
            padding: 24px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            font-size: 28px;
            letter-spacing: 2px;
        }
        .nav {
            background: #141414;
            padding: 16px 32px;
            display: flex;
            gap: 24px;
            border-bottom: 1px solid #222;
        }
        .nav a {
            color: #777;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 4px;
            transition: all 0.2s;
        }
        .nav a:hover, .nav a.active {
            color: #fff;
            background: #222;
        }
        .container {
            padding: 32px;
            max-width: 1400px;
            margin: 0 auto;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: #141414;
            border-radius: 12px;
            padding: 24px;
            border: 1px solid #222;
        }
        .stat-card h3 {
            color: #777;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }
        .stat-card .value {
            font-size: 36px;
            font-weight: bold;
            color: #ff2d55;
        }
        .section {
            background: #141414;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            border: 1px solid #222;
        }
        .section h2 {
            font-size: 20px;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid #222;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #222;
        }
        th {
            color: #777;
            font-weight: 500;
            font-size: 12px;
            text-transform: uppercase;
        }
        tr:hover {
            background: #1a1a1a;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            background: #222;
        }
        .badge.success { background: #1a3d1a; color: #4caf50; }
        .badge.warning { background: #3d3d1a; color: #ffc107; }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background: #ff2d55;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }
        .btn:hover { background: #ff4567; }
        .btn.danger { background: #dc3545; }
        .form-group {
            margin-bottom: 16px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #777;
        }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 12px;
            background: #0a0a0a;
            border: 1px solid #222;
            border-radius: 4px;
            color: #fff;
            font-size: 14px;
        }
        .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: #ff2d55;
        }
        .tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 24px;
        }
        .tab {
            padding: 12px 24px;
            background: #222;
            border: none;
            color: #fff;
            border-radius: 4px;
            cursor: pointer;
        }
        .tab.active {
            background: #ff2d55;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎬 ТРЭШСТРИМ Admin</h1>
        <a href="index.html" class="btn">На сайт</a>
    </div>
    
    <nav>
        <a href="?page=overview" class="active">Обзор</a>
        <a href="?page=favorites">Избранное</a>
        <a href="?page=history">История</a>
        <a href="?page=ratings">Рейтинги</a>
        <a href="?page=comments">Комментарии</a>
        <a href="?page=search">Поиск</a>
        <a href="?page=settings">Настройки</a>
    </nav>
    
    <div class="container">
        <?php
        $page = $_GET['page'] ?? 'overview';
        
        // Получаем статистику
        $stats = [];
        $result = $conn->query("SELECT COUNT(*) as total FROM favorites");
        $stats['favorites'] = $result->fetch_assoc()['total'];
        
        $result = $conn->query("SELECT COUNT(*) as total FROM viewing_history");
        $stats['views'] = $result->fetch_assoc()['total'];
        
        $result = $conn->query("SELECT COUNT(*) as total FROM ratings");
        $stats['ratings'] = $result->fetch_assoc()['total'];
        
        $result = $conn->query("SELECT COUNT(*) as total FROM comments");
        $stats['comments'] = $result->fetch_assoc()['total'];
        
        $result = $conn->query("SELECT COUNT(DISTINCT user_id) as total FROM viewing_history");
        $stats['users'] = $result->fetch_assoc()['total'];
        
        // Обработка действий
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            
            switch ($action) {
                case 'clear_favorites':
                    $conn->query("TRUNCATE TABLE favorites");
                    echo "<p style='color: #4caf50;'>Избранное очищено!</p>";
                    break;
                case 'clear_history':
                    $conn->query("TRUNCATE TABLE viewing_history");
                    echo "<p style='color: #4caf50;'>История очищена!</p>";
                    break;
                case 'clear_ratings':
                    $conn->query("TRUNCATE TABLE ratings");
                    echo "<p style='color: #4caf50;'>Рейтинги очищены!</p>";
                    break;
                case 'clear_comments':
                    $conn->query("TRUNCATE TABLE comments");
                    echo "<p style='color: #4caf50;'>Комментарии очищены!</p>";
                    break;
                case 'clear_search':
                    $conn->query("TRUNCATE TABLE search_history");
                    echo "<p style='color: #4caf50;'>История поиска очищена!</p>";
                    break;
            }
        }
        
        if ($page === 'overview') {
            ?>
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Избранное</h3>
                    <div class="value"><?php echo $stats['favorites']; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Просмотры</h3>
                    <div class="value"><?php echo $stats['views']; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Рейтинги</h3>
                    <div class="value"><?php echo $stats['ratings']; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Комментарии</h3>
                    <div class="value"><?php echo $stats['comments']; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Пользователи</h3>
                    <div class="value"><?php echo $stats['users']; ?></div>
                </div>
            </div>
            
            <div class="section">
                <h2>📈 Топ шоу по просмотрам</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Шоу</th>
                            <th>Просмотров</th>
                            <th>Средний рейтинг</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("
                            SELECT 
                                v.show_slug, 
                                v.show_title, 
                                COUNT(*) as view_count,
                                AVG(r.rating) as avg_rating
                            FROM viewing_history v
                            LEFT JOIN ratings r ON v.show_slug = r.show_slug
                            GROUP BY v.show_slug
                            ORDER BY view_count DESC
                            LIMIT 10
                        ");
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['show_title']) . "</td>";
                            echo "<td>" . $row['view_count'] . "</td>";
                            echo "<td>" . ($row['avg_rating'] ? round($row['avg_rating'], 1) . ' ★' : '-') . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
            <div class="section">
                <h2>👥 Недавняя активность</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Пользователь</th>
                            <th>Шоу</th>
                            <th>Время</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("
                            SELECT * FROM viewing_history 
                            ORDER BY watched_at DESC 
                            LIMIT 10
                        ");
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . substr($row['user_id'], 0, 20) . "...</td>";
                            echo "<td>" . htmlspecialchars($row['show_title']) . "</td>";
                            echo "<td>" . date('d.m.Y H:i', strtotime($row['watched_at'])) . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <?php
        }
        ?>
        
        <?php if ($page === 'favorites'): ?>
        <div class="section">
            <h2>⭐ Избранное</h2>
            <form method="post">
                <input type="hidden" name="action" value="clear_favorites">
                <button type="submit" class="btn danger">Очистить избранное</button>
            </form>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Пользователь</th>
                        <th>Шоу</th>
                        <th>Дата</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $conn->query("SELECT * FROM favorites ORDER BY created_at DESC LIMIT 50");
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['id'] . "</td>";
                        echo "<td>" . substr($row['user_id'], 0, 20) . "...</td>";
                        echo "<td>" . htmlspecialchars($row['show_title']) . "</td>";
                        echo "<td>" . date('d.m.Y H:i', strtotime($row['created_at'])) . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <?php if ($page === 'history'): ?>
        <div class="section">
            <h2>📺 История просмотров</h2>
            <form method="post">
                <input type="hidden" name="action" value="clear_history">
                <button type="submit" class="btn danger">Очистить историю</button>
            </form>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Пользователь</th>
                        <th>Шоу</th>
                        <th>Эпизод</th>
                        <th>Дата</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $conn->query("SELECT * FROM viewing_history ORDER BY watched_at DESC LIMIT 50");
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['id'] . "</td>";
                        echo "<td>" . substr($row['user_id'], 0, 20) . "...</td>";
                        echo "<td>" . htmlspecialchars($row['show_title']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['episode_title'] ?? '-') . "</td>";
                        echo "<td>" . date('d.m.Y H:i', strtotime($row['watched_at'])) . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <?php if ($page === 'ratings'): ?>
        <div class="section">
            <h2>⭐ Рейтинги</h2>
            <form method="post">
                <input type="hidden" name="action" value="clear_ratings">
                <button type="submit" class="btn danger">Очистить рейтинги</button>
            </form>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Пользователь</th>
                        <th>Шоу</th>
                        <th>Рейтинг</th>
                        <th>Дата</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $conn->query("SELECT * FROM ratings ORDER BY created_at DESC LIMIT 50");
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['id'] . "</td>";
                        echo "<td>" . substr($row['user_id'], 0, 20) . "...</td>";
                        echo "<td>" . htmlspecialchars($row['show_slug']) . "</td>";
                        echo "<td>" . str_repeat('★', $row['rating']) . "</td>";
                        echo "<td>" . date('d.m.Y H:i', strtotime($row['created_at'])) . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <?php if ($page === 'comments'): ?>
        <div class="section">
            <h2>💬 Комментарии</h2>
            <form method="post">
                <input type="hidden" name="action" value="clear_comments">
                <button type="submit" class="btn danger">Очистить комментарии</button>
            </form>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Пользователь</th>
                        <th>Шоу</th>
                        <th>Комментарий</th>
                        <th>Дата</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $conn->query("SELECT * FROM comments ORDER BY created_at DESC LIMIT 50");
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['id'] . "</td>";
                        echo "<td>" . substr($row['user_id'], 0, 20) . "...</td>";
                        echo "<td>" . htmlspecialchars($row['show_slug']) . "</td>";
                        echo "<td>" . htmlspecialchars(substr($row['comment_text'], 0, 50)) . "...</td>";
                        echo "<td>" . date('d.m.Y H:i', strtotime($row['created_at'])) . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <?php if ($page === 'search'): ?>
        <div class="section">
            <h2>🔍 История поиска</h2>
            <form method="post">
                <input type="hidden" name="action" value="clear_search">
                <button type="submit" class="btn danger">Очистить поиск</button>
            </form>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Пользователь</th>
                        <th>Запрос</th>
                        <th>Дата</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $conn->query("SELECT * FROM search_history ORDER BY searched_at DESC LIMIT 50");
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['id'] . "</td>";
                        echo "<td>" . substr($row['user_id'], 0, 20) . "...</td>";
                        echo "<td>" . htmlspecialchars($row['search_query']) . "</td>";
                        echo "<td>" . date('d.m.Y H:i', strtotime($row['searched_at'])) . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <?php if ($page === 'settings'): ?>
        <div class="section">
            <h2>⚙️ Настройки пользователей</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Пользователь</th>
                        <th>Тема</th>
                        <th>Уведомления</th>
                        <th>Автоплей</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $conn->query("SELECT * FROM user_settings ORDER BY updated_at DESC LIMIT 50");
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['id'] . "</td>";
                        echo "<td>" . substr($row['user_id'], 0, 20) . "...</td>";
                        echo "<td>" . htmlspecialchars($row['theme']) . "</td>";
                        echo "<td>" . ($row['notifications'] ? 'Вкл' : 'Выкл') . "</td>";
                        echo "<td>" . ($row['autoplay'] ? 'Вкл' : 'Выкл') . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>