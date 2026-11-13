<?php
declare(strict_types=1);

namespace App\Models;

/** E-book: harga per hari. */
class Ebook extends Media
{
    public function __construct(
        string $id,
        string $title,
        string $author,
        float $basePrice,
        int $stock,
        string $description = '',
        private float $fileSizeMb = 0.0
    ) {
        parent::__construct($id, $title, $author, $basePrice, $stock, $description);
    }

    public function getType(): string { return 'ebook'; }

    public function getRentalPrice(int $days): float
    {
        return $this->basePrice * $days;
    }

    public function getExtraInfo(): string
    {
        return $this->fileSizeMb > 0 ? number_format($this->fileSizeMb, 1) . ' MB' : 'PDF/EPUB';
    }

    public function toArray(): array
    {
        return parent::toArray() + ['extra' => $this->fileSizeMb];
    }
}
