<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\User;

class View
{
    public function __construct(private string $viewPath)
    {
    }

    /** @param array<string,mixed> $data */
    public function render(string $view, array $data = [], ?User $user = null): void
    {
        $data['user'] = $user;
        $data['flash'] = Session::pullFlash();
        extract($data, EXTR_SKIP);

        ob_start();
        require $this->viewPath . '/' . $view . '.php';
        $content = (string) ob_get_clean();

        require $this->viewPath . '/layouts/main.php';
    }

    /** Escape output (cegah XSS). */
    public function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    public function rupiah(float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    public function csrf(): string
    {
        return '<input type="hidden" name="_csrf" value="' . $this->e(Session::csrfToken()) . '">';
    }
}
