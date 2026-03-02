<?php
$userName = $user['name'] ?? '';
$userEmail = $user['email'] ?? '';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($title) ?></title>
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
<header class="header">
    <div class="container">
        <div class="brand">Staging Servers Manager</div>
        <nav class="nav">
            <a href="/">ダッシュボード</a>
            <a href="/admin/projects">管理コンソール</a>
            <a href="/logout">ログアウト</a>
        </nav>
        <?php if ($userName || $userEmail): ?>
            <div class="user"><?= h($userName ?: $userEmail) ?></div>
        <?php endif; ?>
    </div>
</header>
<main class="container">
    <?= $content ?>
</main>
<footer class="footer">
    <div class="container">&copy; <?= date('Y') ?> Staging Servers Manager</div>
</footer>
</body>
</html>
