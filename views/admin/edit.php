<section class="page-head">
    <h1>Edit Media</h1>
    <p><a href="index.php?r=admin">&larr; Kembali ke Kelola Katalog</a></p>
</section>

<section class="panel narrow">
    <form method="post" action="index.php?r=admin-update" class="form" data-validate novalidate>
        <?= $this->csrf() ?>
        <input type="hidden" name="media_id" value="<?= $this->e($item->getId()) ?>">

        <label class="field">
            <span>Jenis Media</span>
            <input type="text" value="<?= $this->e($item->getType()->label()) ?>" disabled>
        </label>

        <label class="field">
            <span>Judul</span>
            <input type="text" name="title" value="<?= $this->e($item->getTitle()) ?>" required>
            <small class="error" hidden></small>
        </label>

        <label class="field">
            <span>Penulis</span>
            <input type="text" name="author" value="<?= $this->e($item->getAuthor()) ?>" required>
            <small class="error" hidden></small>
        </label>

        <label class="field">
            <span>Harga Dasar (Rp)</span>
            <input type="number" name="price" min="1" step="100" value="<?= (int) $item->getBasePrice() ?>" required>
            <small class="error" hidden></small>
        </label>

        <label class="field">
            <span>Stok</span>
            <input type="number" name="stock" min="0" value="<?= $item->getStock() ?>" required>
            <small class="error" hidden></small>
        </label>

        <label class="field">
            <span><?= $this->e($item->getType()->extraLabel()) ?></span>
            <?php
            $extraVal = match ($item->getType()) {
                \App\Enums\MediaType::EBOOK => $item->getFileSizeMb(),
                \App\Enums\MediaType::COMIC => $item->getVolume(),
                \App\Enums\MediaType::JOURNAL => $item->getPublisher(),
            };
            ?>
            <input type="text" name="extra" value="<?= $this->e($extraVal) ?>" required>
            <small class="error" hidden></small>
        </label>

        <label class="field">
            <span>Deskripsi</span>
            <textarea name="description" rows="3"><?= $this->e($item->getDescription()) ?></textarea>
        </label>

        <div class="actions" style="justify-content: flex-start; margin-top: 1rem;">
            <button class="btn" type="submit">Simpan Perubahan</button>
            <a class="btn btn-ghost" href="index.php?r=admin">Batal</a>
        </div>
    </form>
</section>
