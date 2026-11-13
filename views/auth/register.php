<section class="narrow">
    <h1>Daftar</h1>
    <form method="post" action="index.php?r=register" class="form" id="register-form" data-validate novalidate>
        <?= $this->csrf() ?>
        <label class="field">
            <span>Nama lengkap</span>
            <input type="text" name="name" required minlength="3" autocomplete="name" value="<?= $this->e($old['name'] ?? '') ?>">
            <small class="error" hidden></small>
        </label>
        <label class="field">
            <span>Email</span>
            <input type="email" name="email" required autocomplete="email" value="<?= $this->e($old['email'] ?? '') ?>">
            <small class="error" hidden></small>
        </label>
        <label class="field">
            <span>Password (min. 6 karakter)</span>
            <input type="password" name="password" id="register-password" required minlength="6" autocomplete="new-password">
            <small class="error" hidden></small>
            <div id="password-strength" class="password-meter" aria-live="polite">
                <div class="meter-bar"><div class="meter-fill" id="meter-fill"></div></div>
                <span class="meter-text small muted" id="meter-text">Kekuatan password</span>
            </div>
        </label>
        <label class="field">
            <span>Ulangi password</span>
            <input type="password" name="confirm" required autocomplete="new-password">
            <small class="error" hidden></small>
        </label>
        <fieldset class="plans">
            <legend>Jenis keanggotaan</legend>
            <label class="radio">
                <input type="radio" name="plan" value="member" <?= ($old['plan'] ?? 'member') === 'member' ? 'checked' : '' ?>>
                <span><strong>Member</strong> — maks. 3 pinjaman aktif</span>
            </label>
            <label class="radio">
                <input type="radio" name="plan" value="premium" <?= ($old['plan'] ?? '') === 'premium' ? 'checked' : '' ?>>
                <span><strong>Premium</strong> — maks. 5 pinjaman, diskon 20%</span>
            </label>
        </fieldset>
        <button class="btn" type="submit">Buat akun</button>
        <p class="muted">Sudah punya akun? <a href="index.php?r=login">Masuk</a></p>
    </form>
</section>
