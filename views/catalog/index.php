<?php
$canRent = $user !== null && !$user->isAdmin();
?>
<section class="page-head">
    <h1>Katalog</h1>
    <p>Sewa e-book, komik digital, dan jurnal untuk 1–14 hari.</p>
</section>

<section class="toolbar" aria-label="Cari dan saring katalog">
    <label class="field">
        <span>Cari judul atau penulis</span>
        <input type="search" id="search" placeholder="Contoh: Laskar">
    </label>
    <label class="field">
        <span>Jenis</span>
        <select id="filter-type">
            <option value="">Semua</option>
            <?php foreach (\App\Enums\MediaType::cases() as $type): ?>
                <option value="<?= $this->e($type->value) ?>"><?= $this->e($type->label()) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label class="check">
        <input type="checkbox" id="only-available"> Hanya yang tersedia
    </label>
</section>

<p id="result-count" class="muted" aria-live="polite"></p>

<section class="grid" id="catalog" aria-label="Daftar media">
    <?php foreach ($items as $m): ?>
        <article class="card type-<?= $this->e($m->getType()->value) ?>"
                 data-title="<?= $this->e(mb_strtolower($m->getTitle() . ' ' . $m->getAuthor())) ?>"
                 data-type="<?= $this->e($m->getType()->value) ?>"
                 data-available="<?= $m->isAvailable() ? '1' : '0' ?>">
            <p class="tag"><?= $this->e($m->getType()->label()) ?> · <?= $this->e($m->getExtraInfo()) ?></p>
            <h2><?= $this->e($m->getTitle()) ?></h2>
            <p class="author"><?= $this->e($m->getAuthor()) ?></p>
            <p class="desc"><?= $this->e($m->getDescription()) ?></p>
            <p class="price"><?= $this->rupiah($m->getBasePrice()) ?></p>
            <p class="muted small"><?= $this->e($m->getType()->rentHint()) ?></p>
            <p class="stock <?= $m->isAvailable() ? '' : 'out' ?>">
                <?= $m->isAvailable() ? 'Stok: ' . $m->getStock() : 'Stok habis' ?>
            </p>
            <?php if ($canRent && $m->isAvailable()): ?>
                <button type="button" class="btn js-rent"
                        data-id="<?= $this->e($m->getId()) ?>"
                        data-title="<?= $this->e($m->getTitle()) ?>"
                        data-type="<?= $this->e($m->getType()->value) ?>"
                        data-price="<?= $this->e($m->getBasePrice()) ?>"
                        data-scheme="<?= $this->e($m->getPricingScheme()) ?>"
                        data-threshold="<?= $this->e($m->getPricingThreshold()) ?>"
                        data-discount="<?= $this->e($m->getPricingDiscount()) ?>">Sewa</button>
            <?php elseif ($user === null && $m->isAvailable()): ?>
                <a class="btn btn-ghost" href="index.php?r=login">Masuk untuk menyewa</a>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
</section>

<p id="empty" class="empty" hidden>Tidak ada media yang cocok. Ubah kata kunci atau filter.</p>

<?php if ($canRent): ?>
<dialog id="rent-dialog" data-discount="<?= $this->e($user->getDiscount()) ?>">
    <form method="post" action="index.php?r=rent" id="rent-form" novalidate>
        <?= $this->csrf() ?>
        <input type="hidden" name="media_id" id="rent-media-id">
        <h2 id="rent-title">Sewa</h2>
        <label class="field">
            <span>Lama sewa (hari)</span>
            <input type="number" name="days" id="rent-days" min="1" max="14" value="3" required>
            <small class="error" id="rent-error" hidden>Isi 1 sampai 14 hari.</small>
        </label>
        <p class="estimate">Perkiraan total: <strong id="rent-total">Rp 0</strong></p>
        <?php if ($user->getDiscount() > 0): ?>
            <p class="muted small">Termasuk diskon member premium <?= (int) ($user->getDiscount() * 100) ?>%.</p>
        <?php endif; ?>
        <div class="actions">
            <button type="button" class="btn btn-ghost" id="rent-cancel">Batal</button>
            <button type="submit" class="btn">Sewa sekarang</button>
        </div>
    </form>
</dialog>
<?php endif; ?>
