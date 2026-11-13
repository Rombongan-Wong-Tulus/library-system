<section class="page-head">
    <h1>Kelola Member</h1>
    <p>Daftar anggota perpustakaan dan pengelolaan status paket keanggotaan.</p>
</section>

<section class="panel">
    <div class="table-wrap">
        <table>
            <caption>Daftar Pengguna Terdaftar</caption>
            <thead>
                <tr>
                    <th scope="col">Nama</th>
                    <th scope="col">Email</th>
                    <th scope="col">Paket</th>
                    <th scope="col">Sewa Aktif</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <th scope="row"><?= $this->e($u->getName()) ?></th>
                    <td><?= $this->e($u->getEmail()) ?></td>
                    <td>
                        <span class="badge <?= $u->isAdmin() ? '' : ($u->getRole()->value === 'premium' ? 'badge-active' : '') ?>">
                            <?= $this->e($u->getRole()->label()) ?>
                        </span>
                    </td>
                    <td><?= (int) ($activeLoans[$u->getId()] ?? 0) ?></td>
                    <td>
                        <?php if (!$u->isAdmin()): ?>
                            <form method="post" action="index.php?r=admin-user-role" class="inline-form"
                                  data-confirm="Ubah paket <?= $this->e($u->getName()) ?> menjadi <?= $u->getRole()->value === 'member' ? 'Premium' : 'Member' ?>?">
                                <?= $this->csrf() ?>
                                <input type="hidden" name="user_id" value="<?= $this->e($u->getId()) ?>">
                                <input type="hidden" name="role" value="<?= $u->getRole()->value === 'member' ? 'premium' : 'member' ?>">
                                <button class="btn btn-small <?= $u->getRole()->value === 'member' ? '' : 'btn-ghost' ?>" type="submit">
                                    <?= $u->getRole()->value === 'member' ? 'Ubah ke Premium' : 'Ubah ke Member' ?>
                                </button>
                            </form>
                        <?php else: ?>
                            <span class="muted small">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
