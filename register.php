<?php
session_start();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<base href="/pregnant-site/">
<title>Регистрация — ТРЭШСТРИМ</title>
<link rel="stylesheet" href="css/index.css">
<link rel="stylesheet" href="css/theme.css">
</head>
<body>
<?php include __DIR__ . '/partials/nav.php'; ?>

<main class="auth-page">
  <form action="auth.php" method="post" class="auth-card">
    <h1>Регистрация</h1>
    <input type="hidden" name="action" value="register">
    <label>
      Логин
      <input type="text" name="username" minlength="3" maxlength="40" required>
    </label>
    <label>
      Email
      <input type="email" name="email" required>
    </label>
    <label>
      Пароль
      <input type="password" name="password" minlength="6" required>
    </label>
    <button type="submit">Создать аккаунт</button>
    <p>Уже есть аккаунт? <a href="login.php">Войти</a></p>
  </form>
</main>

<script src="js/nav-active.js"></script>
<script src="js/theme.js"></script>
</body>
</html>
