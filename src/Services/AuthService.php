<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Session;
use App\Exceptions\ValidationException;
use App\Models\Admin;
use App\Models\Member;
use App\Models\PremiumMember;
use App\Models\User;
use App\Repositories\UserRepository;

class AuthService
{
    public function __construct(private UserRepository $users)
    {
        $this->seedAdmin();
    }

    public function register(string $name, string $email, string $password, string $confirm, string $plan): User
    {
        $errors = [];
        $name = trim($name);
        $email = trim($email);

        if (mb_strlen($name) < 3) {
            $errors[] = 'Nama minimal 3 karakter.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email tidak valid.';
        } elseif ($this->users->findByEmail($email) !== null) {
            $errors[] = 'Email sudah terdaftar.';
        }
        if (strlen($password) < 6) {
            $errors[] = 'Password minimal 6 karakter.';
        }
        if ($password !== $confirm) {
            $errors[] = 'Konfirmasi password tidak sama.';
        }
        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $id = $this->users->nextId();
        $user = $plan === 'premium'
            ? new PremiumMember($id, $name, $email, $hash)
            : new Member($id, $name, $email, $hash);

        $this->users->save($user);
        return $user;
    }

    public function login(string $email, string $password): User
    {
        $user = $this->users->findByEmail(trim($email));
        if ($user === null || !$user->verifyPassword($password)) {
            throw new ValidationException(['Email atau password salah.']);
        }
        Session::set('user_id', $user->getId());
        session_regenerate_id(true);
        return $user;
    }

    public function logout(): void
    {
        Session::destroy();
    }

    public function currentUser(): ?User
    {
        $id = Session::get('user_id');
        return is_string($id) ? $this->users->find($id) : null;
    }

    /** Buat akun admin default saat pertama kali dijalankan. */
    private function seedAdmin(): void
    {
        if ($this->users->all() === []) {
            $this->users->save(new Admin(
                $this->users->nextId(),
                'Administrator',
                'admin@perpus.test',
                password_hash('admin123', PASSWORD_DEFAULT)
            ));
        }
    }
}
