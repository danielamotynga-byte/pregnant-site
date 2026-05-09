<?php
session_start();
require_once __DIR__ . '/db/Database.php';

if (!isset($_SESSION['user_id'], $_SESSION['username'])) {
    $_SESSION['auth_error'] = 'Сначала войдите в аккаунт.';
    header('Location: login.php');
    exit;
}

$favorites = [];
try {
    $db = new Database();
    $favorites = $db->getFavorites((string)$_SESSION['user_id']);
} catch (Exception $e) {
    $favorites = [];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<base href="/pregnant-site/">
<title>Мои шоу — ТРЭШСТРИМ</title>
<link rel="stylesheet" href="css/index.css">
<link rel="stylesheet" href="css/theme.css">
</head>
<body>
<?php include __DIR__ . '/partials/nav.php'; ?>

<main class="profile-page">
  <h1>Мои шоу</h1>
  <p>Здесь сохраняются любимые шоу пользователя <?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?>.</p>

  <section class="favorite-list">
    <?php if (!$favorites): ?>
      <p>Пока нет любимых шоу. Откройте страницу шоу и нажмите “Добавить в любимое”.</p>
    <?php else: ?>
      <?php foreach ($favorites as $favorite): ?>
        <a href="<?php echo htmlspecialchars($favorite['show_slug'], ENT_QUOTES, 'UTF-8'); ?>.php" class="favorite-card">
          <?php echo htmlspecialchars($favorite['show_title'], ENT_QUOTES, 'UTF-8'); ?>
        </a>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>
</main>

<script src="js/nav-active.js"></script>
<script src="js/theme.js"></script>
</body>
</html>
