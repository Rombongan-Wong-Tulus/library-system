<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Core\View;
use App\Enums\Role;
use App\Exceptions\ValidationException;
use App\Factories\UserFactory;
use App\Repositories\MediaRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use DateTimeImmutable;

class AuthController extends BaseController
{
    public function __construct(
        View $view,
        AuthService $auth,
        private ?UserRepository $users = null,
        private ?TransactionRepository $transactions = null,
        private ?MediaRepository $media = null
    ) {
        parent::__construct($view, $auth);
    }

    public function showLogin(): void
    {
        $this->render('auth/login', ['old' => []]);
    }

    public function login(): void
    {
        try {
            $user = $this->auth->login((string) ($_POST['email'] ?? ''), (string) ($_POST['password'] ?? ''));
            Session::flash('success', 'Selamat datang, ' . $user->getName() . '.');
            $this->redirect($user->isAdmin() ? 'admin' : 'catalog');
        } catch (ValidationException $e) {
            foreach ($e->getErrors() as $msg) {
                Session::flash('error', $msg);
            }
            $this->render('auth/login', ['old' => ['email' => $_POST['email'] ?? '']]);
        }
    }

    public function showRegister(): void
    {
        $this->render('auth/register', ['old' => []]);
    }

    public function register(): void
    {
        try {
            $this->auth->register(
                (string) ($_POST['name'] ?? ''),
                (string) ($_POST['email'] ?? ''),
                (string) ($_POST['password'] ?? ''),
                (string) ($_POST['confirm'] ?? ''),
                (string) ($_POST['plan'] ?? 'member')
            );
            Session::flash('success', 'Akun berhasil dibuat. Silakan login.');
            $this->redirect('login');
        } catch (ValidationException $e) {
            foreach ($e->getErrors() as $msg) {
                Session::flash('error', $msg);
            }
            $this->render('auth/register', ['old' => [
                'name' => $_POST['name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'plan' => $_POST['plan'] ?? 'member',
            ]]);
        }
    }

    public function logout(): void
    {
        $this->auth->logout();
        session_start();
        Session::flash('success', 'Kamu sudah keluar.');
        $this->redirect('catalog');
    }

    /** Halaman profil pengguna (data diri, paket, riwayat singkat). */
    public function profile(): void
    {
        $user = $this->requireLogin();
        $recentTrx = [];
        $mediaTitles = [];

        if ($this->transactions !== null) {
            $userTrx = array_reverse($this->transactions->byUser($user->getId()));
            $recentTrx = array_slice($userTrx, 0, 5);
            if ($this->media !== null) {
                foreach ($recentTrx as $t) {
                    $m = $this->media->find($t->getMediaId());
                    $mediaTitles[$t->getId()] = $m ? $m->getTitle() : '(media dihapus)';
                }
            }
        }

        $this->render('auth/profile', [
            'profileUser' => $user,
            'recentTransactions' => $recentTrx,
            'mediaTitles' => $mediaTitles,
        ]);
    }

    /** Tombol upgrade ke paket Premium. */
    public function upgradePlan(): void
    {
        $user = $this->requireLogin();
        if ($user->isAdmin() || $user->getRole() === Role::PREMIUM) {
            Session::flash('error', 'Akun kamu sudah berstatus Premium atau Admin.');
            $this->redirect('profile');
        }

        if ($this->users !== null) {
            $data = $user->toArray();
            $data['role'] = Role::PREMIUM->value;
            $data['updatedAt'] = (new DateTimeImmutable())->format('Y-m-d H:i:s');
            $updated = UserFactory::fromArray($data);
            $this->users->save($updated);
        }

        Session::flash('success', 'Selamat! Akun kamu berhasil di-upgrade ke paket Premium. Nikmati diskon 20% dan batas 5 pinjaman!');
        $this->redirect('profile');
    }
}
