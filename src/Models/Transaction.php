<?php
declare(strict_types=1);

namespace App\Models;

use DateTimeImmutable;

class Transaction
{
    public const LATE_FEE_PER_DAY = 1000.0;

    public function __construct(
        private string $id,
        private string $userId,
        private string $mediaId,
        private string $rentedAt,
        private string $dueAt,
        private float $totalPrice,
        private ?string $returnedAt = null,
        private float $lateFee = 0.0
    ) {
    }

    public function getId(): string { return $this->id; }
    public function getUserId(): string { return $this->userId; }
    public function getMediaId(): string { return $this->mediaId; }
    public function getRentedAt(): string { return $this->rentedAt; }
    public function getDueAt(): string { return $this->dueAt; }
    public function getTotalPrice(): float { return $this->totalPrice; }
    public function getReturnedAt(): ?string { return $this->returnedAt; }
    public function getLateFee(): float { return $this->lateFee; }

    public function isActive(): bool
    {
        return $this->returnedAt === null;
    }

    public function isOverdue(): bool
    {
        return $this->isActive() && new DateTimeImmutable() > new DateTimeImmutable($this->dueAt);
    }

    /** Tandai dikembalikan dan hitung denda keterlambatan. */
    public function markReturned(): void
    {
        $now = new DateTimeImmutable();
        $due = new DateTimeImmutable($this->dueAt);
        $this->returnedAt = $now->format('Y-m-d H:i:s');
        if ($now > $due) {
            $lateDays = (int) ceil(($now->getTimestamp() - $due->getTimestamp()) / 86400);
            $this->lateFee = $lateDays * self::LATE_FEE_PER_DAY;
        }
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'mediaId' => $this->mediaId,
            'rentedAt' => $this->rentedAt,
            'dueAt' => $this->dueAt,
            'totalPrice' => $this->totalPrice,
            'returnedAt' => $this->returnedAt,
            'lateFee' => $this->lateFee,
        ];
    }

    /** @param array<string,mixed> $d */
    public static function fromArray(array $d): self
    {
        return new self(
            $d['id'], $d['userId'], $d['mediaId'], $d['rentedAt'], $d['dueAt'],
            (float) $d['totalPrice'], $d['returnedAt'] ?? null, (float) ($d['lateFee'] ?? 0)
        );
    }
}
