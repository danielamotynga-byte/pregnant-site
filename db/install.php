<?php
/**
 * Database Installer - ТРЭШСТРИМ
 * Простой установщик базы данных
 */

$host = 'localhost';
$user = 'root';
$password = '';
$database = 'treshstream';

$message = '';
$error = '';

// Подключаемся без выбора БД
$conn = new mysqli($host, $user, $password);

if ($conn->connect_error) {
    $error = " Не удалось подключиться к MySQL: " . $conn->connect_error;
} else {
    // Создаем базу данных
    if ($conn->query("CREATE DATABASE IF NOT EXISTS $database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
        $message .= " База данных '$database' создана<br>";
    } else {
        $error .= " Ошибка создания БД: " . $conn->error . "<br>";
    }
    
    // Выбираем БД
    $conn->select_db($database);
    $conn->set_charset('utf8mb4');
    
    // Создаем таблицы
    $tables = [
        'users' => "
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(40) NOT NULL UNIQUE,
                email VARCHAR(120) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ",
        'favorites' => "
            CREATE TABLE IF NOT EXISTS favorites (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id VARCHAR(64) NOT NULL,
                show_slug VARCHAR(64) NOT NULL,
                show_title VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_favorite (user_id, show_slug),
                INDEX idx_user (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ",
        'viewing_history' => "
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ",
        'ratings' => "
            CREATE TABLE IF NOT EXISTS ratings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id VARCHAR(64) NOT NULL,
                show_slug VARCHAR(64) NOT NULL,
                rating TINYINT NOT NULL CHECK (rating >= 1 AND rating <= 5),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_rating (user_id, show_slug),
                INDEX idx_show (show_slug)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ",
        'search_history' => "
            CREATE TABLE IF NOT EXISTS search_history (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id VARCHAR(64) NOT NULL,
                search_query VARCHAR(255) NOT NULL,
                searched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user (user_id),
                INDEX idx_query (search_query(100))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ",
        'comments' => "
            CREATE TABLE IF NOT EXISTS comments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id VARCHAR(64) NOT NULL,
                show_slug VARCHAR(64) NOT NULL,
                comment_text TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_show (show_slug),
                INDEX idx_user (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ",
        'user_settings' => "
            CREATE TABLE IF NOT EXISTS user_settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id VARCHAR(64) NOT NULL UNIQUE,
                theme VARCHAR(16) DEFAULT 'dark',
                notifications BOOLEAN DEFAULT TRUE,
                autoplay BOOLEAN DEFAULT TRUE,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        "
    ];
    
    foreach ($tables as $name => $sql) {
        if ($conn->query($sql)) {
            $message .= " Таблица '$name' создана<br>";
        } else {
            $error .= "❌ Ошибка создания таблицы '$name': " . $conn->error . "<br>";
        }
    }
    
    // Добавляем тестовые данные
    $conn->query("INSERT IGNORE INTO favorites (user_id, show_slug, show_title) VALUES 
        ('demo_user', 'pregnant16', 'Беременна в 16'),
        ('demo_user', 'deceived', 'Беременна по обману'),
        ('demo_user', 'khata', 'Хата на тата')");
    
    $conn->query("INSERT IGNORE INTO ratings (user_id, show_slug, rating) VALUES 
        ('demo_user', 'pregnant16', 5),
        ('demo_user', 'deceived', 4),
        ('demo_user', 'khata', 5)");
    
    $message .= " Тестовые данные добавлены<br>";
    
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Установка БД - ТРЭШСТРИМ</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #0a0a0a;
            color: #f0f0f0;
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: #141414;
            border-radius: 16px;
            padding: 48px;
            max-width: 600px;
            width: 90%;
            border: 1px solid #222;
        }
        h1 {
            font-size: 32px;
            margin-bottom: 8px;
            background: linear-gradient(135deg, #fff 0%, #ff9500 50%, #ff2d55 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .subtitle {
            color: #777;
            margin-bottom: 32px;
        }
        .message {
            background: #1a1a1a;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
            line-height: 1.8;
        }
        .message.success {
            border-left: 4px solid #4caf50;
        }
        .message.error {
            border-left: 4px solid #f44336;
        }
        .btn {
            display: inline-block;
            padding: 14px 28px;
            background: linear-gradient(135deg, #ff2d55, #ff9500);
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font-size: 16px;
            font-weight: 500;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: scale(1.05);
        }
        .info {
            margin-top: 32px;
            padding: 16px;
            background: #0a0a0a;
            border-radius: 8px;
        }
        .info h3 {
            color: #ff9500;
            margin-bottom: 12px;
        }
        .info code {
            background: #222;
            padding: 2px 6px;
            border-radius: 4px;
            color: #ff2d55;
        }
        .steps {
            margin-top: 24px;
        }
        .step {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #222;
        }
        .step:last-child {
            border-bottom: none;
        }
        .step-icon {
            width: 32px;
            height: 32px;
            background: #222;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }
        .step.done .step-icon {
            background: #4caf50;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎬 ТРЭШСТРИМ</h1>
        <p class="subtitle">Установка базы данных</p>
        
        <?php if ($error): ?>
        <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($message): ?>
        <div class="message success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (!$error && $conn): ?>
        <a href="admin.php" class="btn">Открыть админ-панель →</a>
        <?php else: ?>
        <a href="?retry=1" class="btn">Повторить установку</a>
        <?php endif; ?>
        
        <div class="steps">
            <div class="step <?php echo strpos($message, 'БД') !== false ? 'done' : ''; ?>">
                <div class="step-icon">1</div>
                <div>Создание базы данных</div>
            </div>
            <div class="step <?php echo substr_count($message, 'Таблица') >= 6 ? 'done' : ''; ?>">
                <div class="step-icon">2</div>
                <div>Создание таблиц (6 штук)</div>
            </div>
            <div class="step <?php echo strpos($message, 'Тестовые') !== false ? 'done' : ''; ?>">
                <div class="step-icon">3</div>
                <div>Добавление тестовых данных</div>
            </div>
        </div>
        
        <div class="info">
            <h3>📁 Файлы базы данных</h3>
            <ul>
                <li><code>db/setup.php</code> - Установщик</li>
                <li><code>db/Database.php</code> - Класс работы с БД</li>
                <li><code>db/api.php</code> - API endpoints</li>
                <li><code>db/admin.php</code> - Админ-панель</li>
                <li><code>db/database.sql</code> - SQL дамп</li>
                <li><code>js/api.js</code> - JavaScript клиент</li>
            </ul>
        </div>
        
        <div class="info">
            <h3>🔧 Возможности</h3>
            <ul>
                <li>⭐ Избранное - сохранять любимые шоу</li>
                <li>📺 История просмотров</li>
                <li>★ Рейтинг шоу (1-5 звёзд)</li>
                <li>🔍 История поиска</li>
                <li>💬 Комментарии</li>
                <li>⚙️ Настройки пользователей</li>
            </ul>
        </div>
    </div>
</body>
</html>
