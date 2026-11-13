<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Core\View;
use App\Factories\MediaFactory;
use App\Repositories\MediaRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use InvalidArgumentException;

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

    public function addMedia(): void
    {
        $this->requireAdmin();
        $title = trim((string) ($_POST['title'] ?? ''));
        $author = trim((string) ($_POST['author'] ?? ''));
        $price = (float) ($_POST['price'] ?? 0);
        $stock = (int) ($_POST['stock'] ?? 0);

        if ($title === '' || $author === '' || $price <= 0 || $stock < 0) {
            Session::flash('error', 'Judul, penulis, harga (> 0), dan stok wajib diisi dengan benar.');
            $this->redirect('admin');
        }

        try {
            $media = MediaFactory::fromArray([
                'id' => $this->media->nextId(),
                'type' => (string) ($_POST['type'] ?? 'ebook'),
                'title' => $title,
                'author' => $author,
                'basePrice' => $price,
                'stock' => $stock,
                'description' => trim((string) ($_POST['description'] ?? '')),
                'extra' => $_POST['extra'] ?? null,
            ]);
            $this->media->save($media);
            Session::flash('success', '"' . $title . '" ditambahkan ke katalog.');
        } catch (InvalidArgumentException $e) {
            Session::flash('error', $e->getMessage());
        }
        $this->redirect('admin');
    }

    public function deleteMedia(): void
    {
        $this->requireAdmin();
        $this->media->delete((string) ($_POST['media_id'] ?? ''));
        Session::flash('success', 'Media dihapus dari katalog.');
        $this->redirect('admin');
    }
}
