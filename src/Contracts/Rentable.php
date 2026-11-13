<?php
declare(strict_types=1);

namespace App\Contracts;

/**
 * Kontrak untuk semua media yang dapat disewa di sistem perpustakaan.
 */
interface Rentable
{
    public function getId(): string;

    public function getTitle(): string;

    public function getRentalPrice(int $days): float;

    public function isAvailable(): bool;

    public function getStock(): int;
}
