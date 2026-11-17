<section class="page-head">
    <h1>Profil Saya</h1>
    <p>Informasi akun dan status keanggotaan perpustakaan.</p>
</section>

<div class="profile-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <section class="panel">
        <h2>Data Diri</h2>
        <table style="min-width: 100%;">
            <tbody>
                <tr>
                    <th scope="row" style="width: 40%;">Nama Lengkap</th>
                    <td><?= $this->e($profileUser->getName()) ?></td>
                </tr>
                <tr>
                    <th scope="row">Email</th>
                    <td><?= $this->e($profileUser->getEmail()) ?></td>
                </tr>
                <tr>
                    <th scope="row">Paket Keanggotaan</th>
                    <td>
                        <span class="badge <?= $profileUser->getRole()->value === 'premium' ? 'badge-active' : '' ?>">
                            <?= $this->e($profileUser->getRole()->label()) ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Batas Pinjaman</th>
                    <td>Maksimal <?= (int) $profileUser->getMaxLoans() ?> buku aktif</td>
                </tr>
                <tr>
                    <th scope="row">Diskon Sewa</th>
                    <td><?= (int) ($profileUser->getDiscount() * 100) ?>%</td>
                </tr>
                <?php if ($profileUser->getCreatedAt()): ?>
                <tr>
                    <th scope="row">Terdaftar Sejak</th>
                    <td><?= $this->e(date('d M Y', strtotime($profileUser->getCreatedAt()))) ?></td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ($profileUser->getRole()->value === 'member'): ?>
            <div class="upgrade-card">
                <h3>Tingkatkan ke Premium</h3>
                <p class="small">Dapatkan diskon sewa 20% untuk semua transaksi dan pinjam hingga 5 buku sekaligus.</p>
                <form method="post" action="index.php?r=upgrade-plan" data-confirm="Yakin ingin meningkatkan paket akun kamu ke Premium?">
                    <?= $this->csrf() ?>
                    <button class="btn btn-small" type="submit">Upgrade ke Premium Sekarang</button>
                </form>
            </div>
        <?php endif; ?>
    </section>

    <section class="panel">
        <h2>Riwayat Sewa Singkat</h2>
        <?php if ($recentTransactions === []): ?>
            <p class="empty">Belum ada riwayat transaksi sewa.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table style="min-width: 100%;">
                    <thead>
                        <tr>
                            <th scope="col">Media</th>
                            <th scope="col">Tgl Sewa</th>
                            <th scope="col">Total</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($recentTransactions as $t): ?>
                        <tr>
                            <th scope="row"><?= $this->e($mediaTitles[$t->getId()] ?? '-') ?></th>
                            <td><?= $this->e(date('d/m/Y', strtotime($t->getRentedAt()))) ?></td>
                            <td><?= $this->rupiah($t->getTotalPrice() + $t->getLateFee()) ?></td>
                            <td>
                                <?php if (!$t->isActive()): ?>
                                    <span class="badge">Selesai</span>
                                <?php elseif ($t->isOverdue()): ?>
                                    <span class="badge badge-late">Terlambat</span>
                                <?php else: ?>
                                    <span class="badge badge-active">Aktif</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p style="margin-top: .75rem;"><a class="small" href="index.php?r=rentals">Lihat semua sewaan saya &rarr;</a></p>
        <?php endif; ?>
    </section>
</div>
