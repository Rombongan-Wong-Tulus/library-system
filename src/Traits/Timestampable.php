<?php
declare(strict_types=1);

namespace App\Traits;

use DateTimeImmutable;

/**
 * Trait untuk mencatat waktu pembuatan dan pembaruan entitas.
 */
trait Timestampable
{
    protected ?string $createdAt = null;
    protected ?string $updatedAt = null;

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    public function setCreatedAt(?string $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function setUpdatedAt(?string $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * Inisialisasi timestamp dengan kompatibilitas data lama.
     */
    public function initTimestamps(?string $created = null, ?string $updated = null): void
    {
        $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        $this->createdAt = $created ?? $now;
        $this->updatedAt = $updated ?? $this->createdAt;
    }

    /**
     * Perbarui waktu pembaruan terakhir.
     */
    public function touch(): void
    {
        $this->updatedAt = (new DateTimeImmutable())->format('Y-m-d H:i:s');
    }

    /**
     * Format timestamp untuk serialisasi ke array/JSON.
     * @return array<string, ?string>
     */
    protected function timestampArray(): array
    {
        return [
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
