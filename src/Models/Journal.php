<?php
declare(strict_types=1);

namespace App\Models;

/** Jurnal ilmiah: tarif flat berapa pun lama sewa. */
class Journal extends Media
{
    public function __construct(
        string $id,
        string $title,
        string $author,
        float $basePrice,
        int $stock,
        string $description = '',
        private string $publisher = '-'
    ) {
        parent::__construct($id, $title, $author, $basePrice, $stock, $description);
    }

    public function getType(): string { return 'journal'; }

    public function getRentalPrice(int $days): float
    {
        return $this->basePrice;
    }

    public function getExtraInfo(): string
    {
        return $this->publisher;
    }

    public function toArray(): array
    {
        return parent::toArray() + ['extra' => $this->publisher];
    }
}
