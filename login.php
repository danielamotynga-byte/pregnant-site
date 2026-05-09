<?php
session_start();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<base href="/pregnant-site/">
<title>Вход — ТРЭШСТРИМ</title>
<link rel="stylesheet" href="css/index.css">
<link rel="stylesheet" href="css/theme.css">
</head>
<body>
<?php include __DIR__ . '/partials/nav.php'; ?>

<main class="auth-page">
  <form action="auth.php" method="post" class="auth-card">
    <h1>Вход</h1>
    <input type="hidden" name="action" value="login">
    <label>
      Логин или email
      <input type="text" name="login" required>
    </label>
    <label>
      Пароль
      <input type="password" name="password" required>
    </label>
    <button type="submit">Войти</button>
    <p>Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
  </form>
</main>

<script src="js/nav-active.js"></script>
<script src="js/theme.js"></script>
</body>
</html>
