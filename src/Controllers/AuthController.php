<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Exceptions\ValidationException;

class AuthController extends BaseController
{
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
}
