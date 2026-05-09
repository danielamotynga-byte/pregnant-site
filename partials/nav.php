<?php
$authMessage = $_SESSION['auth_message'] ?? '';
$authError = $_SESSION['auth_error'] ?? '';
unset($_SESSION['auth_message'], $_SESSION['auth_error']);
$currentUser = $_SESSION['username'] ?? '';
?>

<nav>
  <span>ТРЭШСТРИМ</span>
  <a href="index.php">Главная</a>
  <a href="pregnant16.php">Беременна в 16</a>
  <a href="deceived.php">Беременна по обману</a>
  <a href="muzhskoe.php">Мужское / Женское</a>
  <a href="khata.php">Хата на тата</a>
  <a href="weddings.php">4 свадьбы</a>
  <a href="waited.php">Ждули</a>
  <a href="top.php">ТОП ТРЭША</a>
  <a href="admin.php">Админ</a>

  <div class="nav-auth">
    <?php if ($currentUser): ?>
      <a href="profile.php" class="auth-user"><?php echo htmlspecialchars($currentUser, ENT_QUOTES, 'UTF-8'); ?></a>
      <form action="auth.php" method="post" class="auth-logout">
        <input type="hidden" name="action" value="logout">
        <button type="submit">Выйти</button>
      </form>
    <?php else: ?>
      <a href="login.php" class="auth-link">Вход</a>
      <a href="register.php" class="auth-link reg-btn">Регистрация</a>
    <?php endif; ?>
  </div>
</nav>

<?php if ($authMessage || $authError): ?>
  <div class="auth-notice <?php echo $authError ? 'auth-notice-error' : ''; ?>">
    <?php echo htmlspecialchars($authError ?: $authMessage, ENT_QUOTES, 'UTF-8'); ?>
  </div>
<?php endif; ?>
