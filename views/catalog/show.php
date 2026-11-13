<?php
$canRent = $user !== null && !$user->isAdmin();
?>
<section class="page-head">
    <p><a href="index.php?r=catalog">&larr; Kembali ke Katalog</a></p>
    <h1><?= $this->e($media->getTitle()) ?></h1>
    <p class="author">Karya: <strong><?= $this->e($media->getAuthor()) ?></strong></p>
</section>

<div class="detail-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
    <div class="detail-cover-card" style="background: var(--surface); border: 1px solid var(--line); border-radius: var(--radius); padding: 1.5rem; text-align: center;">
        <div class="cover-art cover-<?= $this->e($media->getType()->value) ?>" style="height: 220px; display: flex; flex-direction: column; align-items: center; justify-content: center; border-radius: var(--radius); margin-bottom: 1.25rem;">
            <span class="cover-icon" aria-hidden="true" style="font-size: 3rem;">
                <?= match ($media->getType()) {
                    \App\Enums\MediaType::EBOOK => '📖',
                    \App\Enums\MediaType::COMIC => '🎨',
                    \App\Enums\MediaType::JOURNAL => '📑',
                } ?>
            </span>
            <span class="cover-type-tag" style="margin-top: .5rem; font-weight: 700; text-transform: uppercase; font-size: .8rem; letter-spacing: .05em;">
                <?= $this->e($media->getType()->label()) ?>
            </span>
        </div>

        <p class="price" style="font-size: 1.4rem; margin: .5rem 0;"><?= $this->rupiah($media->getBasePrice()) ?></p>
        <p class="muted small" style="margin-bottom: 1rem;"><?= $this->e($media->getType()->rentHint()) ?></p>

        <p class="stock <?= $media->isAvailable() ? '' : 'out' ?>" style="font-weight: 600; margin-bottom: 1.25rem;">
            <?= $media->isAvailable() ? 'Tersedia (Stok: ' . $media->getStock() . ')' : 'Stok sedang habis' ?>
        </p>

        <div style="display: flex; gap: .75rem; justify-content: center; flex-wrap: wrap;">
            <button type="button" class="btn btn-ghost" id="btn-read-preview">
                📖 Baca Cuplikan
            </button>

            <?php if ($canRent && $media->isAvailable()): ?>
                <button type="button" class="btn js-rent"
                        data-id="<?= $this->e($media->getId()) ?>"
                        data-title="<?= $this->e($media->getTitle()) ?>"
                        data-type="<?= $this->e($media->getType()->value) ?>"
                        data-price="<?= $this->e($media->getBasePrice()) ?>"
                        data-scheme="<?= $this->e($media->getPricingScheme()) ?>"
                        data-threshold="<?= $this->e($media->getPricingThreshold()) ?>"
                        data-discount="<?= $this->e($media->getPricingDiscount()) ?>">
                    Sewa Sekarang
                </button>
            <?php elseif ($user === null && $media->isAvailable()): ?>
                <a class="btn" href="index.php?r=login">Masuk untuk Menyewa</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="detail-info-card" style="background: var(--surface); border: 1px solid var(--line); border-radius: var(--radius); padding: 1.5rem;">
        <h2>Informasi Media</h2>
        <table style="min-width: 100%; margin-bottom: 1.5rem;">
            <tbody>
                <tr>
                    <th scope="row" style="width: 35%;">ID Media</th>
                    <td><code><?= $this->e($media->getId()) ?></code></td>
                </tr>
                <tr>
                    <th scope="row">Kategori</th>
                    <td>
                        <span class="badge"><?= $this->e($media->getType()->label()) ?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?= $this->e($media->getType()->extraLabel()) ?></th>
                    <td><?= $this->e($media->getExtraInfo()) ?></td>
                </tr>
                <tr>
                    <th scope="row">Biaya Sewa</th>
                    <td><?= $this->rupiah($media->getBasePrice()) ?> <span class="muted small">(<?= $this->e($media->getType()->rentHint()) ?>)</span></td>
                </tr>
                <?php if ($media->getCreatedAt()): ?>
                <tr>
                    <th scope="row">Ditambahkan</th>
                    <td><?= $this->e(date('d M Y', strtotime($media->getCreatedAt()))) ?></td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <h2>Sinopsis / Deskripsi</h2>
        <p style="line-height: 1.7; color: var(--ink);">
            <?= nl2br($this->e($media->getDescription() !== '' ? $media->getDescription() : 'Belum ada deskripsi untuk media ini.')) ?>
        </p>
    </div>
</div>

<!-- Modal Pratinjau Teks Statis (Mock Baca) -->
<dialog id="read-dialog" style="max-width: 600px; width: 90vw;">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--line); padding-bottom: .8rem; margin-bottom: 1rem;">
        <h2 style="margin: 0; font-size: 1.25rem;">Pratinjau: <?= $this->e($media->getTitle()) ?></h2>
        <button type="button" class="btn btn-ghost btn-small" id="btn-close-read" aria-label="Tutup pratinjau">&times;</button>
    </div>
    <div style="max-height: 55vh; overflow-y: auto; padding: .5rem; line-height: 1.7; font-size: .95rem; background: var(--paper); border-radius: var(--radius);">
        <p><strong>[Halaman Contoh — Pratinjau Digital]</strong></p>
        <p>
            Selamat datang di pembaca digital Lembar Library. Ini adalah pratinjau cuplikan teks resmi dari
            <em><?= $this->e($media->getTitle()) ?></em> oleh <strong><?= $this->e($media->getAuthor()) ?></strong>.
        </p>
        <p>
            <em>"Setiap lembar buku adalah gerbang menuju cakrawala baru. Ilmu yang dibaca dan dipahami
            akan menjadi pelita penuntun dalam mengarungi dinamika kehidupan akademis dan karya nyata."</em>
        </p>
        <p>
            <?= match ($media->getType()) {
                \App\Enums\MediaType::EBOOK => "Bab 1 — Pendahuluan:\nE-book ini menyajikan pembahasan tuntas dengan format digital berkualitas tinggi, dilengkapi studi kasus praktis dan rangkuman komprehensif di setiap akhir bab.",
                \App\Enums\MediaType::COMIC => "Panel Pembuka:\nGoresan ilustrasi komik menghidupkan kisah dengan dialog cerdas dan visual ekspresif. Ikuti petualangan serunya bab demi bab!",
                \App\Enums\MediaType::JOURNAL => "Abstrak Artikel:\nPenelitian ini mengkaji penerapan rekayasa perangkat lunak dan arsitektur berorientasi objek dalam membangun sistem informasi perpustakaan yang efisien, terukur, dan aman.",
            } ?>
        </p>
        <p class="muted small">Untuk membaca seluruh naskah lengkap, silakan sewa buku ini melalui tombol sewa di atas.</p>
    </div>
    <div class="actions" style="margin-top: 1rem;">
        <button type="button" class="btn" id="btn-close-read-footer">Tutup Pratinjau</button>
    </div>
</dialog>

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
