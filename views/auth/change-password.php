<section class="narrow">
    <h1>Ubah Password</h1>
    <p class="small muted">Password baru minimal 6 karakter dan harus berbeda dari password lama.</p>

    <form method="post" action="index.php?r=change-password" class="form" id="change-password-form" data-validate novalidate>
        <?= $this->csrf() ?>
        <label class="field">
            <span>Password Saat Ini</span>
            <input type="password" name="current_password" required autocomplete="current-password">
            <small class="error" hidden></small>
        </label>
        <label class="field">
            <span>Password Baru (min. 6 karakter)</span>
            <input type="password" name="password" id="change-password-new" required minlength="6" autocomplete="new-password">
            <small class="error" hidden></small>
            <div id="password-strength" class="password-meter" aria-live="polite">
                <div class="meter-bar"><div class="meter-fill" id="meter-fill"></div></div>
                <span class="meter-text small muted" id="meter-text">Kekuatan password</span>
            </div>
        </label>
        <label class="field">
            <span>Ulangi Password Baru</span>
            <input type="password" name="confirm" required autocomplete="new-password">
            <small class="error" hidden></small>
        </label>
        <button class="btn" type="submit">Ubah Password</button>
        <p class="muted"><a href="index.php?r=profile">Kembali ke Profil</a></p>
    </form>
</section>