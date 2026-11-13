<?php
declare(strict_types=1);

namespace App\Models;

use App\Enums\MediaType;

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

    public function getType(): MediaType
    {
        return MediaType::JOURNAL;
    }

    public function getPricingScheme(): string
    {
        return 'flat';
    }

    public function getRentalPrice(int $days): float
    {
        return $this->basePrice;
    }

    public function getExtraInfo(): string
    {
        return $this->publisher;
    }

    public function getPublisher(): string
    {
        return $this->publisher;
    }

    public function setPublisher(string $publisher): void
    {
        $this->publisher = $publisher;
        $this->touch();
    }

    public function toArray(): array
    {
        return parent::toArray() + ['extra' => $this->publisher];
    }
}
