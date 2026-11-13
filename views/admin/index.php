<section class="page-head">
    <h1>Kelola katalog</h1>
</section>

<section class="panel">
    <h2>Tambah media</h2>
    <form method="post" action="index.php?r=admin-add" class="form form-grid" data-validate novalidate>
        <?= $this->csrf() ?>
        <label class="field">
            <span>Jenis</span>
            <select name="type" id="admin-type">
                <option value="ebook">E-book</option>
                <option value="comic">Komik</option>
                <option value="journal">Jurnal</option>
            </select>
        </label>
        <label class="field">
            <span>Judul</span>
            <input type="text" name="title" required>
            <small class="error" hidden></small>
        </label>
        <label class="field">
            <span>Penulis</span>
            <input type="text" name="author" required>
            <small class="error" hidden></small>
        </label>
        <label class="field">
            <span>Harga dasar (Rp)</span>
            <input type="number" name="price" min="1" step="100" required>
            <small class="error" hidden></small>
        </label>
        <label class="field">
            <span>Stok</span>
            <input type="number" name="stock" min="0" value="1" required>
            <small class="error" hidden></small>
        </label>
        <label class="field">
            <span id="extra-label">Ukuran file (MB)</span>
            <input type="text" name="extra" id="admin-extra">
        </label>
        <label class="field wide">
            <span>Deskripsi singkat</span>
            <textarea name="description" rows="2"></textarea>
        </label>
        <div class="wide"><button class="btn" type="submit">Simpan ke katalog</button></div>
    </form>
</section>

<section class="panel">
    <h2>Isi katalog (<?= count($items) ?>)</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th scope="col">Judul</th><th scope="col">Jenis</th><th scope="col">Harga</th><th scope="col">Stok</th><th scope="col"><span class="sr-only">Aksi</span></th></tr>
            </thead>
            <tbody>
            <?php foreach ($items as $m): ?>
                <tr>
                    <th scope="row"><?= $this->e($m->getTitle()) ?></th>
                    <td><?= $this->e($m->getType()) ?></td>
                    <td><?= $this->rupiah($m->getBasePrice()) ?></td>
                    <td><?= $m->getStock() ?></td>
                    <td>
                        <form method="post" action="index.php?r=admin-delete" data-confirm="Hapus &quot;<?= $this->e($m->getTitle()) ?>&quot; dari katalog?">
                            <?= $this->csrf() ?>
                            <input type="hidden" name="media_id" value="<?= $this->e($m->getId()) ?>">
                            <button class="btn btn-ghost btn-small" type="submit">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="panel">
    <h2>Semua transaksi (<?= count($transactions) ?>)</h2>
    <?php if ($transactions === []): ?>
        <p class="empty">Belum ada transaksi.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th scope="col">ID</th><th scope="col">Pengguna</th><th scope="col">Media</th><th scope="col">Jatuh tempo</th><th scope="col">Biaya</th><th scope="col">Status</th></tr>
            </thead>
            <tbody>
            <?php foreach ($transactions as $t): ?>
                <tr>
                    <th scope="row"><?= $this->e($t->getId()) ?></th>
                    <td><?= $this->e($userNames[$t->getUserId()] ?? '-') ?></td>
                    <td><?= $this->e($mediaTitles[$t->getMediaId()] ?? '(dihapus)') ?></td>
                    <td><?= $this->e(date('d M Y', strtotime($t->getDueAt()))) ?></td>
                    <td><?= $this->rupiah($t->getTotalPrice() + $t->getLateFee()) ?></td>
                    <td><?= $t->isActive() ? ($t->isOverdue() ? 'Terlambat' : 'Aktif') : 'Dikembalikan' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</section>
