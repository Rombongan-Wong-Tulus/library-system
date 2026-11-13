<section class="narrow">
    <h1>Masuk</h1>
    <form method="post" action="index.php?r=login" class="form" data-validate novalidate>
        <?= $this->csrf() ?>
        <label class="field">
            <span>Email</span>
            <input type="email" name="email" required autocomplete="email" value="<?= $this->e($old['email'] ?? '') ?>">
            <small class="error" hidden></small>
        </label>
        <label class="field">
            <span>Password</span>
            <input type="password" name="password" required autocomplete="current-password">
            <small class="error" hidden></small>
        </label>
        <button class="btn" type="submit">Masuk</button>
        <p class="muted">Belum punya akun? <a href="index.php?r=register">Daftar</a></p>
    </form>
</section>
