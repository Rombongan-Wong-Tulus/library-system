<?php
$canRent = $user !== null && !$user->isAdmin();
$q = $q ?? '';
$filterType = $filterType ?? '';
$onlyAvail = $onlyAvail ?? false;
$sort = $sort ?? 'title_asc';
$currentPage = $currentPage ?? 1;
$totalPages = $totalPages ?? 1;
$totalItems = $totalItems ?? count($items);
?>
<section class="hero-band">
    <div class="hero-eyebrow">Perpustakaan Digital</div>
    <h1>Katalog</h1>
    <p>Sewa e-book, komik digital, dan jurnal untuk 1–14 hari.</p>
</section>

<form method="get" action="index.php" class="toolbar" id="catalog-toolbar" aria-label="Cari dan saring katalog">
    <input type="hidden" name="r" value="catalog">

    <label class="field" style="flex: 2 1 200px;">
        <span>Cari judul, penulis, deskripsi</span>
        <input type="search" name="q" id="search" placeholder="Contoh: PHP, Laskar..." value="<?= $this->e($q) ?>">
    </label>

    <label class="field" style="flex: 1 1 140px;">
        <span>Jenis</span>
        <select name="type" id="filter-type">
            <option value="">Semua</option>
            <?php foreach (\App\Enums\MediaType::cases() as $type): ?>
                <option value="<?= $this->e($type->value) ?>" <?= $filterType === $type->value ? 'selected' : '' ?>>
                    <?= $this->e($type->label()) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>

    <label class="field" style="flex: 1 1 150px;">
        <span>Urutkan</span>
        <select name="sort" id="sort-select">
            <option value="title_asc" <?= $sort === 'title_asc' ? 'selected' : '' ?>>Judul (A - Z)</option>
            <option value="title_desc" <?= $sort === 'title_desc' ? 'selected' : '' ?>>Judul (Z - A)</option>
            <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Harga Termurah</option>
            <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Harga Termahal</option>
            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Terbaru</option>
        </select>
    </label>

    <label class="check" style="align-self: center; margin-top: .8rem;">
        <input type="checkbox" name="available" value="1" id="only-available" <?= $onlyAvail ? 'checked' : '' ?>>
        <span>Tersedia saja</span>
    </label>

    <div style="align-self: flex-end; margin-bottom: .9rem;">
        <button class="btn btn-small" type="submit">Filter</button>
        <?php if ($q !== '' || $filterType !== '' || $onlyAvail || $sort !== 'title_asc'): ?>
            <a class="btn btn-ghost btn-small" href="index.php?r=catalog">Reset</a>
        <?php endif; ?>
    </div>
</form>

<p id="result-count" class="muted" aria-live="polite">
    Menampilkan <?= count($items) ?> dari <?= $totalItems ?> media (Halaman <?= $currentPage ?> dari <?= $totalPages ?>)
</p>

<section class="grid" id="catalog" aria-label="Daftar media">
    <?php foreach ($items as $m): ?>
        <article class="card type-<?= $this->e($m->getType()->value) ?>"
                 data-title="<?= $this->e(mb_strtolower($m->getTitle() . ' ' . $m->getAuthor())) ?>"
                 data-type="<?= $this->e($m->getType()->value) ?>"
                 data-available="<?= $m->isAvailable() ? '1' : '0' ?>">
            
            <div class="card-cover cover-<?= $this->e($m->getType()->value) ?>" aria-hidden="true">
                <span class="cover-icon">
                    <?= match ($m->getType()) {
                        \App\Enums\MediaType::EBOOK => '📖',
                        \App\Enums\MediaType::COMIC => '🎨',
                        \App\Enums\MediaType::JOURNAL => '📑',
                    } ?>
                </span>
                <span class="cover-type-tag"><?= $this->e($m->getType()->label()) ?></span>
            </div>

            <p class="tag"><?= $this->e($m->getType()->label()) ?> · <?= $this->e($m->getExtraInfo()) ?></p>
            <h2>
                <a href="index.php?r=media&id=<?= urlencode($m->getId()) ?>" class="card-title-link">
                    <?= $this->e($m->getTitle()) ?>
                </a>
            </h2>
            <p class="author"><?= $this->e($m->getAuthor()) ?></p>
            <p class="desc"><?= $this->e($m->getDescription()) ?></p>
            <p class="price"><?= $this->rupiah($m->getBasePrice()) ?></p>
            <p class="muted small"><?= $this->e($m->getType()->rentHint()) ?></p>
            
            <p class="stock <?= $m->isAvailable() ? '' : 'out' ?>">
                <span class="badge <?= $m->isAvailable() ? 'badge-active' : 'badge-late' ?>">
                    <?= $m->isAvailable() ? 'Tersedia (' . $m->getStock() . ')' : 'Stok habis' ?>
                </span>
            </p>

            <div class="card-actions" style="display: flex; gap: .5rem; margin-top: auto; padding-top: .5rem;">
                <a class="btn btn-ghost btn-small" href="index.php?r=media&id=<?= urlencode($m->getId()) ?>">Detail</a>
                <?php if ($canRent && $m->isAvailable()): ?>
                    <button type="button" class="btn btn-small js-rent"
                            data-id="<?= $this->e($m->getId()) ?>"
                            data-title="<?= $this->e($m->getTitle()) ?>"
                            data-type="<?= $this->e($m->getType()->value) ?>"
                            data-price="<?= $this->e($m->getBasePrice()) ?>"
                            data-scheme="<?= $this->e($m->getPricingScheme()) ?>"
                            data-threshold="<?= $this->e($m->getPricingThreshold()) ?>"
                            data-discount="<?= $this->e($m->getPricingDiscount()) ?>">Sewa</button>
                <?php elseif ($user === null && $m->isAvailable()): ?>
                    <a class="btn btn-ghost btn-small" href="index.php?r=login">Masuk</a>
                <?php endif; ?>
            </div>
        </article>
    <?php endforeach; ?>
</section>

<p id="empty" class="empty" <?= count($items) === 0 ? '' : 'hidden' ?>>
    Tidak ada media yang cocok. Ubah kata kunci atau filter.
</p>

<?php if ($totalPages > 1): ?>
    <nav class="pagination" aria-label="Navigasi Halaman" style="display: flex; justify-content: center; gap: .5rem; margin-top: 2rem;">
        <?php
        $buildUrl = static function (int $p) use ($q, $filterType, $onlyAvail, $sort): string {
            return 'index.php?' . http_build_query([
                'r' => 'catalog',
                'q' => $q,
                'type' => $filterType,
                'available' => $onlyAvail ? '1' : '0',
                'sort' => $sort,
                'page' => $p,
            ]);
        };
        ?>
        <?php if ($currentPage > 1): ?>
            <a class="btn btn-ghost btn-small" href="<?= $this->e($buildUrl($currentPage - 1)) ?>">&larr; Sebelumnya</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a class="btn btn-small <?= $i === $currentPage ? '' : 'btn-ghost' ?>" href="<?= $this->e($buildUrl($i)) ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>

        <?php if ($currentPage < $totalPages): ?>
            <a class="btn btn-ghost btn-small" href="<?= $this->e($buildUrl($currentPage + 1)) ?>">Berikutnya &rarr;</a>
        <?php endif; ?>
    </nav>
<?php endif; ?>

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
