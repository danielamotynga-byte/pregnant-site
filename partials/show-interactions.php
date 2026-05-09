<?php
$showSlug = $showSlug ?? '';
$showTitle = $showTitle ?? '';
$isLoggedIn = isset($_SESSION['user_id'], $_SESSION['username']);
$isFavorite = false;
$comments = [];

if ($db && $showSlug) {
    try {
        if ($isLoggedIn) {
            $isFavorite = $db->isFavorite((string)$_SESSION['user_id'], $showSlug);
        }
        $comments = $db->getComments($showSlug, 20);
    } catch (Exception $e) {
        $comments = [];
    }
}
?>

<section class="show-actions-panel">
  <div class="show-actions-head">
    <h2>Моё отношение к шоу</h2>
    <?php if ($isLoggedIn): ?>
      <form action="show-action.php" method="post">
        <input type="hidden" name="show_slug" value="<?php echo htmlspecialchars($showSlug, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="show_title" value="<?php echo htmlspecialchars($showTitle, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="action" value="<?php echo $isFavorite ? 'remove_favorite' : 'add_favorite'; ?>">
        <button type="submit" class="favorite-toggle"><?php echo $isFavorite ? 'В любимых' : 'Добавить в любимое'; ?></button>
      </form>
    <?php else: ?>
      <a class="favorite-toggle" href="login.php">Войти, чтобы добавить в любимое</a>
    <?php endif; ?>
  </div>

  <div class="comments-block">
    <h2>Комментарии</h2>
    <?php if ($isLoggedIn): ?>
      <form action="show-action.php" method="post" class="comment-form">
        <input type="hidden" name="action" value="add_comment">
        <input type="hidden" name="show_slug" value="<?php echo htmlspecialchars($showSlug, ENT_QUOTES, 'UTF-8'); ?>">
        <textarea name="comment" rows="3" placeholder="Напишите своё мнение о шоу или выпуске" required></textarea>
        <button type="submit">Отправить</button>
      </form>
    <?php else: ?>
      <p><a href="login.php">Войдите</a>, чтобы написать комментарий.</p>
    <?php endif; ?>

    <div class="comment-list">
      <?php if (!$comments): ?>
        <p>Комментариев пока нет. Можно быть первым.</p>
      <?php else: ?>
        <?php foreach ($comments as $comment): ?>
          <article class="comment-item">
            <strong><?php echo htmlspecialchars($comment['username'] ?: 'Пользователь', ENT_QUOTES, 'UTF-8'); ?></strong>
            <p><?php echo nl2br(htmlspecialchars($comment['comment_text'], ENT_QUOTES, 'UTF-8')); ?></p>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>
