<?php
declare(strict_types=1);

namespace App\Models;

use App\Enums\Role;
use App\Traits\Timestampable;

/**
 * Kelas abstrak pengguna. Password disimpan sebagai hash (private).
 */
abstract class User
{
    use Timestampable;

    public function __construct(
        protected readonly string $id,
        protected string $name,
        protected string $email,
        private string $passwordHash
    ) {
        $this->initTimestamps();
    }

    abstract public function getRole(): Role;

    /** Batas peminjaman aktif bersamaan. */
    abstract public function getMaxLoans(): int;

    /** Diskon (0.0 - 1.0) pada harga sewa. */
    abstract public function getDiscount(): float;

    public function getId(): string { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getEmail(): string { return $this->email; }

    public function setName(string $name): void { $this->name = $name; $this->touch(); }
    public function setEmail(string $email): void { $this->email = $email; $this->touch(); }
    public function setPasswordHash(string $hash): void { $this->passwordHash = $hash; $this->touch(); }

    public function verifyPassword(string $plain): bool
    {
        return password_verify($plain, $this->passwordHash);
    }

    public function isAdmin(): bool
    {
        return $this->getRole() === Role::ADMIN;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'role' => $this->getRole()->value,
            'name' => $this->name,
            'email' => $this->email,
            'passwordHash' => $this->passwordHash,
        ] + $this->timestampArray();
    }

    public function __toString(): string
    {
        return sprintf('%s <%s> [%s]', $this->name, $this->email, $this->getRole()->label());
    }
}
