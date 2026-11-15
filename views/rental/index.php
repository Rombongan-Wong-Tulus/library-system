<section class="page-head">
    <h1>Sewaan saya</h1>
    <p>Maksimal <?= (int) $user->getMaxLoans() ?> pinjaman aktif untuk akun <?= $this->e($user->getRole()->label()) ?>.</p>
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
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <form method="post" action="index.php?r=return" data-confirm="Kembalikan &quot;<?= $this->e($m ? $m->getTitle() : '') ?>&quot;?">
                                <?= $this->csrf() ?>
                                <input type="hidden" name="trx_id" value="<?= $this->e($t->getId()) ?>">
                                <button class="btn btn-small btn-ghost" style="width: 100%;" type="submit">Kembalikan</button>
                            </form>
                            
                            <?php if ($row['maxExtendDays'] > 0): ?>
                                <form method="post" action="index.php?r=extend-rental" style="display: flex; gap: 4px;">
                                    <?= $this->csrf() ?>
                                    <input type="hidden" name="transaction_id" value="<?= $this->e($t->getId()) ?>">
                                    <label for="extra_days_<?= $this->e($t->getId()) ?>" class="sr-only">Hari tambahan</label>
                                    <select name="extra_days" id="extra_days_<?= $this->e($t->getId()) ?>" class="input-small" style="height: 32px; padding: 0 4px; font-size: 12px; flex: 1;">
                                        <?php for ($i = 1; $i <= $row['maxExtendDays']; $i++): ?>
                                            <option value="<?= $i ?>"><?= $i ?> hari</option>
                                        <?php endfor; ?>
                                    </select>
                                    <button class="btn btn-small btn-primary" type="submit">Perpanjang</button>
                                </form>
                            <?php elseif ($t->isActive() && !$t->isOverdue() && $t->getTotalDays() >= 14): ?>
                                <span class="small" style="color: var(--color-mute);">Maks 14 hari tercapai</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
