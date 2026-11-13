<section class="page-head">
    <h1>Sewaan saya</h1>
    <p>Maksimal <?= (int) $user->getMaxLoans() ?> pinjaman aktif untuk akun <?= $this->e($user->getRole()) ?>.</p>
</section>

<?php if ($rows === []): ?>
    <p class="empty">Belum ada sewaan. <a href="index.php?r=catalog">Pilih dari katalog</a>.</p>
<?php else: ?>
<div class="table-wrap">
    <table>
        <caption class="sr-only">Riwayat sewa</caption>
        <thead>
            <tr>
                <th scope="col">Judul</th>
                <th scope="col">Disewa</th>
                <th scope="col">Jatuh tempo</th>
                <th scope="col">Biaya</th>
                <th scope="col">Status</th>
                <th scope="col"><span class="sr-only">Aksi</span></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $row): $t = $row['trx']; $m = $row['media']; ?>
            <tr>
                <th scope="row"><?= $this->e($m ? $m->getTitle() : '(media dihapus)') ?></th>
                <td><?= $this->e(date('d M Y', strtotime($t->getRentedAt()))) ?></td>
                <td><?= $this->e(date('d M Y H:i', strtotime($t->getDueAt()))) ?></td>
                <td>
                    <?= $this->rupiah($t->getTotalPrice()) ?>
                    <?php if ($t->getLateFee() > 0): ?>
                        <br><span class="small error-text">+ denda <?= $this->rupiah($t->getLateFee()) ?></span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (!$t->isActive()): ?>
                        <span class="badge">Dikembalikan</span>
                    <?php elseif ($t->isOverdue()): ?>
                        <span class="badge badge-late">Terlambat</span>
                    <?php else: ?>
                        <span class="badge badge-active">Aktif</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($t->isActive()): ?>
                        <form method="post" action="index.php?r=return" data-confirm="Kembalikan &quot;<?= $this->e($m ? $m->getTitle() : '') ?>&quot;?">
                            <?= $this->csrf() ?>
                            <input type="hidden" name="trx_id" value="<?= $this->e($t->getId()) ?>">
                            <button class="btn btn-small" type="submit">Kembalikan</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
