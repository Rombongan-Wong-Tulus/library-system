<?php
declare(strict_types=1);

namespace App\Models;

use App\Enums\MediaType;

/** Komik digital: harga per hari, diskon jika sewa >= batas hari tertentu. */
class Comic extends Media
{
    public const DISCOUNT_THRESHOLD_DAYS = 7;
    public const LONG_TERM_DISCOUNT_RATE = 0.15;

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

    public function getType(): MediaType
    {
        return MediaType::COMIC;
    }

    public function getPricingScheme(): string
    {
        return 'daily_discount';
    }

    public function getPricingThreshold(): int
    {
        return self::DISCOUNT_THRESHOLD_DAYS;
    }

    public function getPricingDiscount(): float
    {
        return self::LONG_TERM_DISCOUNT_RATE;
    }

    public function getRentalPrice(int $days): float
    {
        $total = $this->basePrice * $days;
        return $days >= self::DISCOUNT_THRESHOLD_DAYS
            ? $total * (1 - self::LONG_TERM_DISCOUNT_RATE)
            : $total;
    }

    public function getExtraInfo(): string
    {
        return 'Volume ' . $this->volume;
    }

    public function getVolume(): int
    {
        return $this->volume;
    }

    public function setVolume(int $volume): void
    {
        $this->volume = $volume;
        $this->touch();
    }

    public function toArray(): array
    {
        return parent::toArray() + ['extra' => $this->volume];
    }
}
