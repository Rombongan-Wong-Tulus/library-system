<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Core\View;
use App\Models\User;
use App\Services\AuthService;

abstract class BaseController
{
    public function __construct(
        protected View $view,
        protected AuthService $auth
    ) {
    }

    protected function user(): ?User
    {
        return $this->auth->currentUser();
    }

    /** @param array<string,mixed> $data */
    protected function render(string $view, array $data = []): void
    {
        $this->view->render($view, $data, $this->user());
    }

    protected function redirect(string $route): never
    {
        // ponytail: perutean sederhana berbasis index.php?r=
        header('Location: index.php?r=' . $route);
        exit;
    }

    protected function requireLogin(): User
    {
        $user = $this->user();
        if ($user === null) {
            Session::flash('error', 'Silakan login terlebih dahulu.');
            $this->redirect('login');
        }
        return $user;
    }

    protected function requireAdmin(): User
    {
        $user = $this->requireLogin();
        if (!$user->isAdmin()) {
            Session::flash('error', 'Halaman ini khusus admin.');
            $this->redirect('catalog');
        }
        return $user;
    }
}
