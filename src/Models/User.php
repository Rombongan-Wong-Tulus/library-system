<?php
declare(strict_types=1);

namespace App\Models;

/**
 * Kelas abstrak pengguna. Password disimpan sebagai hash (private).
 */
abstract class User
{
    public function __construct(
        protected string $id,
        protected string $name,
        protected string $email,
        private string $passwordHash
    ) {
    }

    abstract public function getRole(): string;

    /** Batas peminjaman aktif bersamaan. */
    abstract public function getMaxLoans(): int;

    /** Diskon (0.0 - 1.0) pada harga sewa. */
    abstract public function getDiscount(): float;

    public function getId(): string { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getEmail(): string { return $this->email; }

    public function verifyPassword(string $plain): bool
    {
        return password_verify($plain, $this->passwordHash);
    }

    public function isAdmin(): bool
    {
        return $this->getRole() === 'admin';
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'role' => $this->getRole(),
            'name' => $this->name,
            'email' => $this->email,
            'passwordHash' => $this->passwordHash,
        ];
    }
}
