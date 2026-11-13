<?php
declare(strict_types=1);

namespace App\Contracts;

/** Kontrak untuk semua media yang bisa disewa. */
interface Rentable
{
    public function getRentalPrice(int $days): float;

    public function isAvailable(): bool;
}
