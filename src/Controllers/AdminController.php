<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Core\View;
use App\Enums\MediaType;
use App\Enums\Role;
use App\Factories\MediaFactory;
use App\Factories\UserFactory;
use App\Models\Comic;
use App\Models\Ebook;
use App\Models\Journal;
use App\Models\Transaction;
use App\Repositories\MediaRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use DateTimeImmutable;
use InvalidArgumentException;
use ValueError;

class AdminController extends BaseController
{
    public function __construct(
        View $view,
        AuthService $auth,
        private MediaRepository $media,
        private TransactionRepository $transactions,
        private UserRepository $users
    ) {
        parent::__construct($view, $auth);
    }

    public function dashboard(): void
    {
        $this->requireAdmin();
        $names = [];
        foreach ($this->users->all() as $u) {
            $names[$u->getId()] = $u->getName();
        }
        $titles = [];
        foreach ($this->media->all() as $m) {
            $titles[$m->getId()] = $m->getTitle();
        }
        $this->render('admin/index', [
            'items' => $this->media->all(),
            'transactions' => array_reverse($this->transactions->all()),
            'userNames' => $names,
            'mediaTitles' => $titles,
        ]);
    }

    /**
     * Validasi kolom extra di server berdasarkan jenis media:
     * - e-book: angka float > 0
     * - komik: bilangan bulat int >= 1
     * - jurnal: teks tidak kosong
     * @return string[] Pesan error jika ada
     */
    private function validateExtra(MediaType $type, mixed $extra): array
    {
        $errors = [];
        $val = trim((string) ($extra ?? ''));

        if ($type === MediaType::EBOOK) {
            if ($val === '' || !is_numeric($val) || (float) $val <= 0) {
                $errors[] = 'Ukuran file e-book harus berupa angka lebih dari 0 MB.';
            }
        } elseif ($type === MediaType::COMIC) {
            if ($val === '' || !filter_var($val, FILTER_VALIDATE_INT) || (int) $val < 1) {
                $errors[] = 'Nomor volume komik harus berupa bilangan bulat minimal 1.';
            }
        } elseif ($type === MediaType::JOURNAL) {
            if ($val === '') {
                $errors[] = 'Nama penerbit jurnal tidak boleh kosong.';
            }
        }

        return $errors;
    }

    public function addMedia(): void
    {
        $this->requireAdmin();
        $title = trim((string) ($_POST['title'] ?? ''));
        $author = trim((string) ($_POST['author'] ?? ''));
        $price = (float) ($_POST['price'] ?? 0);
        $stock = (int) ($_POST['stock'] ?? 0);
        $typeRaw = (string) ($_POST['type'] ?? 'ebook');
        $extra = $_POST['extra'] ?? null;

        if ($title === '' || $author === '' || $price <= 0 || $stock < 0) {
            Session::flash('error', 'Judul, penulis, harga (> 0), dan stok wajib diisi dengan benar.');
            $this->redirect('admin');
        }

        try {
            $typeEnum = MediaType::from($typeRaw);
        } catch (ValueError) {
            Session::flash('error', 'Jenis media tidak valid.');
            $this->redirect('admin');
        }

        $extraErrors = $this->validateExtra($typeEnum, $extra);
        if ($extraErrors !== []) {
            foreach ($extraErrors as $err) {
                Session::flash('error', $err);
            }
            $this->redirect('admin');
        }

        try {
            $media = MediaFactory::fromArray([
                'id' => $this->media->nextId(),
                'type' => $typeEnum->value,
                'title' => $title,
                'author' => $author,
                'basePrice' => $price,
                'stock' => $stock,
                'description' => trim((string) ($_POST['description'] ?? '')),
                'extra' => $extra,
            ]);
            $this->media->save($media);
            Session::flash('success', '"' . $title . '" ditambahkan ke katalog.');
        } catch (InvalidArgumentException $e) {
            Session::flash('error', $e->getMessage());
        }
        $this->redirect('admin');
    }

    public function editMediaForm(): void
    {
        $this->requireAdmin();
        $id = (string) ($_GET['id'] ?? '');
        $item = $this->media->find($id);
        if ($item === null) {
            Session::flash('error', 'Media tidak ditemukan.');
            $this->redirect('admin');
        }

        $this->render('admin/edit', ['item' => $item]);
    }

    public function updateMedia(): void
    {
        $this->requireAdmin();
        $id = (string) ($_POST['media_id'] ?? '');
        $item = $this->media->find($id);
        if ($item === null) {
            Session::flash('error', 'Media tidak ditemukan.');
            $this->redirect('admin');
        }

        $title = trim((string) ($_POST['title'] ?? ''));
        $author = trim((string) ($_POST['author'] ?? ''));
        $price = (float) ($_POST['price'] ?? 0);
        $stock = (int) ($_POST['stock'] ?? 0);
        $description = trim((string) ($_POST['description'] ?? ''));
        $extra = $_POST['extra'] ?? null;

        if ($title === '' || $author === '' || $price <= 0 || $stock < 0) {
            Session::flash('error', 'Judul, penulis, harga (> 0), dan stok wajib diisi dengan benar.');
            $this->redirect('admin-edit&id=' . urlencode($id));
        }

        $extraErrors = $this->validateExtra($item->getType(), $extra);
        if ($extraErrors !== []) {
            foreach ($extraErrors as $err) {
                Session::flash('error', $err);
            }
            $this->redirect('admin-edit&id=' . urlencode($id));
        }

        $item->setTitle($title);
        $item->setAuthor($author);
        $item->setBasePrice($price);
        $item->setStock($stock);
        $item->setDescription($description);

        if ($item instanceof Ebook) {
            $item->setFileSizeMb((float) $extra);
        } elseif ($item instanceof Comic) {
            $item->setVolume((int) $extra);
        } elseif ($item instanceof Journal) {
            $item->setPublisher(trim((string) $extra));
        }

        $this->media->save($item);
        Session::flash('success', 'Media "' . $title . '" berhasil diperbarui.');
        $this->redirect('admin');
    }

    public function deleteMedia(): void
    {
        $this->requireAdmin();
        $mediaId = (string) ($_POST['media_id'] ?? '');

        // Larang hapus media yang masih punya transaksi aktif
        $activeTrx = array_filter(
            $this->transactions->all(),
            static fn(Transaction $t): bool => $t->getMediaId() === $mediaId && $t->isActive()
        );

        if ($activeTrx !== []) {
            Session::flash('error', 'Tidak dapat menghapus media karena masih memiliki ' . count($activeTrx) . ' transaksi sewa aktif.');
            $this->redirect('admin');
        }

        $this->media->delete($mediaId);
        Session::flash('success', 'Media dihapus dari katalog.');
        $this->redirect('admin');
    }

    /** Halaman daftar member untuk admin. */
    public function members(): void
    {
        $this->requireAdmin();
        $activeLoans = [];
        foreach ($this->transactions->all() as $t) {
            if ($t->isActive()) {
                $uid = $t->getUserId();
                $activeLoans[$uid] = ($activeLoans[$uid] ?? 0) + 1;
            }
        }

        $this->render('admin/members', [
            'users' => $this->users->all(),
            'activeLoans' => $activeLoans,
        ]);
    }

    /** Ubah paket Member <-> Premium. */
    public function updateUserRole(): void
    {
        $this->requireAdmin();
        $userId = (string) ($_POST['user_id'] ?? '');
        $targetRole = (string) ($_POST['role'] ?? '');

        $targetUser = $this->users->find($userId);
        if ($targetUser === null) {
            Session::flash('error', 'Pengguna tidak ditemukan.');
            $this->redirect('admin-members');
        }

        if ($targetUser->isAdmin()) {
            Session::flash('error', 'Role administrator tidak dapat diubah.');
            $this->redirect('admin-members');
        }

        if (!in_array($targetRole, ['member', 'premium'], true)) {
            Session::flash('error', 'Role yang dipilih tidak valid.');
            $this->redirect('admin-members');
        }

        $data = $targetUser->toArray();
        $data['role'] = $targetRole;
        $data['updatedAt'] = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        $updated = UserFactory::fromArray($data);
        $this->users->save($updated);

        Session::flash('success', 'Paket pengguna ' . $targetUser->getName() . ' berhasil diubah menjadi ' . $updated->getRole()->label() . '.');
        $this->redirect('admin-members');
    }
}
