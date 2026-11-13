<?php
declare(strict_types=1);

namespace App\Models;

/** Komik digital: harga per hari, diskon 15% jika sewa >= 7 hari. */
class Comic extends Media
{
    public function __construct(
        string $id,
        string $title,
        string $author,
        float $basePrice,
        int $stock,
        string $description = '',
        private int $volume = 1
    ) {
        parent::__construct($id, $title, $author, $basePrice, $stock, $description);
    }

    public function getType(): string { return 'comic'; }

    public function getRentalPrice(int $days): float
    {
        $total = $this->basePrice * $days;
        return $days >= 7 ? $total * 0.85 : $total;
    }

    public function getExtraInfo(): string
    {
        return 'Volume ' . $this->volume;
    }

    public function toArray(): array
    {
        return parent::toArray() + ['extra' => $this->volume];
    }
}
