<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lembar — Sewa E-book</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/app.js" defer></script>
</head>
<body>
<a class="skip" href="#konten">Lewati ke konten</a>

<header class="site-header">
    <a class="brand" href="index.php?r=catalog">Lembar</a>
    <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="menu">Menu</button>
    <nav id="menu" aria-label="Navigasi utama">
        <a href="index.php?r=catalog">Katalog</a>
        <?php if ($user === null): ?>
            <a href="index.php?r=login">Masuk</a>
            <a class="btn btn-small" href="index.php?r=register">Daftar</a>
        <?php else: ?>
            <?php if ($user->isAdmin()): ?>
                <a href="index.php?r=admin">Katalog</a>
                <a href="index.php?r=admin-members">Member</a>
            <?php else: ?>
                <a href="index.php?r=rentals">Sewaan saya</a>
                <a href="index.php?r=profile">Profil</a>
            <?php endif; ?>
            <form method="post" action="index.php?r=logout" class="inline-form">
                <?= $this->csrf() ?>
                <button class="btn btn-ghost btn-small" type="submit">Keluar (<?= $this->e($user->getName()) ?>)</button>
            </form>
        <?php endif; ?>
    </nav>
</header>

<main id="konten">
    <?php foreach ($flash as $msg): ?>
        <p class="flash flash-<?= $this->e($msg['type']) ?>" role="status"><?= $this->e($msg['message']) ?></p>
    <?php endforeach; ?>
    <?= $content ?>
</main>

<footer class="site-footer">
    <p>Lembar — prototipe UTS Pemrograman Web, Informatika UMM 2026/2027.</p>
</footer>
</body>
</html>
