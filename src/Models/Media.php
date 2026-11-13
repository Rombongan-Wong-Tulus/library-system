<?php
declare(strict_types=1);

namespace App\Models;

use App\Contracts\Rentable;
use App\Enums\MediaType;
use App\Traits\Timestampable;
use JsonSerializable;

/**
 * Kelas abstrak induk untuk semua media di katalog.
 * Menerapkan enkapsulasi, trait waktu, dan kontrak Rentable.
 */
abstract class Media implements Rentable, JsonSerializable
{
    use Timestampable;

    public function __construct(
        protected readonly string $id,
        protected string $title,
        protected string $author,
        protected float $basePrice,
        protected int $stock,
        protected string $description = ''
    ) {
        $this->initTimestamps();
    }

    // ---- Polymorphism: tiap subclass punya implementasi sendiri ----
    abstract public function getType(): MediaType;

    abstract public function getRentalPrice(int $days): float;

    /** Info tambahan khusus tipe (ukuran file, volume, penerbit). */
    abstract public function getExtraInfo(): string;

    /** Skema perhitungan harga sewa untuk klien (frontend). */
    public function getPricingScheme(): string
    {
        return 'daily';
    }

    public function getPricingThreshold(): int
    {
        return 0;
    }

    public function getPricingDiscount(): float
    {
        return 0.0;
    }

    // ---- Getter ----
    public function getId(): string { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getAuthor(): string { return $this->author; }
    public function getBasePrice(): float { return $this->basePrice; }
    public function getStock(): int { return $this->stock; }
    public function getDescription(): string { return $this->description; }

    // ---- Setter untuk edit admin ----
    public function setTitle(string $title): void { $this->title = $title; $this->touch(); }
    public function setAuthor(string $author): void { $this->author = $author; $this->touch(); }
    public function setBasePrice(float $basePrice): void { $this->basePrice = $basePrice; $this->touch(); }
    public function setStock(int $stock): void { $this->stock = $stock; $this->touch(); }
    public function setDescription(string $description): void { $this->description = $description; $this->touch(); }

    public function isAvailable(): bool
    {
        return $this->stock > 0;
    }

    public function decreaseStock(): void
    {
        if ($this->stock > 0) {
            $this->stock--;
            $this->touch();
        }
    }

    public function increaseStock(): void
    {
        $this->stock++;
        $this->touch();
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->getType()->value,
            'title' => $this->title,
            'author' => $this->author,
            'basePrice' => $this->basePrice,
            'stock' => $this->stock,
            'description' => $this->description,
        ] + $this->timestampArray();
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function __toString(): string
    {
        return sprintf('%s (%s) oleh %s', $this->title, $this->getType()->label(), $this->author);
    }
}
