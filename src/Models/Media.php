<?php
declare(strict_types=1);

namespace App\Models;

use App\Contracts\Rentable;
use JsonSerializable;

/**
 * Kelas abstrak induk untuk semua media di katalog.
 * Properti dibuat protected/private (encapsulation), akses lewat getter.
 */
abstract class Media implements Rentable, JsonSerializable
{
    public function __construct(
        protected string $id,
        protected string $title,
        protected string $author,
        protected float $basePrice,
        protected int $stock,
        protected string $description = ''
    ) {
    }

    // ---- Polymorphism: tiap subclass punya implementasi sendiri ----
    abstract public function getType(): string;

    abstract public function getRentalPrice(int $days): float;

    /** Info tambahan khusus tipe (ukuran file, volume, penerbit). */
    abstract public function getExtraInfo(): string;

    // ---- Getter ----
    public function getId(): string { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getAuthor(): string { return $this->author; }
    public function getBasePrice(): float { return $this->basePrice; }
    public function getStock(): int { return $this->stock; }
    public function getDescription(): string { return $this->description; }

    public function isAvailable(): bool
    {
        return $this->stock > 0;
    }

    public function decreaseStock(): void
    {
        if ($this->stock > 0) {
            $this->stock--;
        }
    }

    public function increaseStock(): void
    {
        $this->stock++;
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->getType(),
            'title' => $this->title,
            'author' => $this->author,
            'basePrice' => $this->basePrice,
            'stock' => $this->stock,
            'description' => $this->description,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
