<?php
declare(strict_types=1);

namespace App\Models;

use App\Traits\Timestampable;
use DateTimeImmutable;

class Transaction
{
    use Timestampable;

    public const LATE_FEE_PER_DAY = 1000;

    public function __construct(
        private readonly string $id,
        private readonly string $userId,
        private readonly string $mediaId,
        private readonly string $rentedAt,
        private string $dueAt,
        private readonly int $totalPrice,
        private ?string $returnedAt = null,
        private int $lateFee = 0,
        private int $totalDays = 0,
        private int $totalPaid = 0,
        private int $extensionCount = 0,
        private ?int $basePriceAtRental = null
    ) {
        $this->initTimestamps($this->rentedAt, $this->returnedAt ?? $this->rentedAt);
    }

    public function getId(): string { return $this->id; }
    public function getUserId(): string { return $this->userId; }
    public function getMediaId(): string { return $this->mediaId; }
    public function getRentedAt(): string { return $this->rentedAt; }
    public function getDueAt(): string { return $this->dueAt; }
    public function getTotalPrice(): int { return $this->totalPrice; }
    public function getReturnedAt(): ?string { return $this->returnedAt; }
    public function getLateFee(): int { return $this->lateFee; }
    public function getTotalDays(): int { return $this->totalDays; }
    public function getTotalPaid(): int { return $this->totalPaid; }
    public function getExtensionCount(): int { return $this->extensionCount; }
    public function getBasePriceAtRental(): ?int { return $this->basePriceAtRental; }

    public function isActive(): bool
    {
        return $this->returnedAt === null;
    }

    public function isOverdue(): bool
    {
        return $this->isActive() && (new DateTimeImmutable()) > (new DateTimeImmutable($this->dueAt));
    }

    public const MAX_TOTAL_DAYS = 14;

    public function assertExtendable(int $extraDays, DateTimeImmutable $now): void
    {
        if (!$this->isActive()) {
            throw new \App\Exceptions\RentalException('Transaksi sudah dikembalikan dan tidak bisa diperpanjang.');
        }
        if ($now > new DateTimeImmutable($this->dueAt)) {
            throw new \App\Exceptions\RentalException('Transaksi sudah terlambat dan tidak bisa diperpanjang.');
        }
        if ($extraDays < 1) {
            throw new \App\Exceptions\RentalException('Durasi perpanjangan minimal 1 hari.');
        }
        if ($this->totalDays + $extraDays > self::MAX_TOTAL_DAYS) {
            throw new \App\Exceptions\RentalException('Total durasi sewa kumulatif maksimal 14 hari.');
        }
    }

    /**
     * Memperpanjang masa sewa transaksi.
     *
     * @param int $extraDays Jumlah hari perpanjangan.
     * @param int $newPrice Harga total baru setelah perpanjangan (dihitung oleh PriceCalculator).
     * @param DateTimeImmutable $now Waktu saat ini.
     * @return int Biaya tambahan yang perlu dibayar (additionalFee).
     * @throws \App\Exceptions\RentalException Jika perpanjangan tidak valid.
     */
    public function extend(int $extraDays, int $newPrice, DateTimeImmutable $now): int
    {
        $this->assertExtendable($extraDays, $now);

        $oldDueAt = new DateTimeImmutable($this->dueAt);
        $this->dueAt = $oldDueAt->modify("+$extraDays days")->format('Y-m-d H:i:s');
        $this->totalDays += $extraDays;
        $this->extensionCount++;

        $additionalFee = max(0, $newPrice - $this->totalPaid);
        $this->totalPaid = max($this->totalPaid, $newPrice);

        $this->touch();
        return $additionalFee;
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
        $this->touch();
    }

    /** @return array<string, mixed> */
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
            'totalDays' => $this->totalDays,
            'totalPaid' => $this->totalPaid,
            'extensionCount' => $this->extensionCount,
            'basePriceAtRental' => $this->basePriceAtRental,
        ] + $this->timestampArray();
    }

    /** @param array<string, mixed> $d */
    public static function fromArray(array $d): self
    {
        $rentedAt = (string) $d['rentedAt'];
        $dueAt = (string) $d['dueAt'];
        $totalPrice = (int) ($d['totalPrice'] ?? 0);

        // Fallback totalDays: hitung dari rentedAt ke dueAt
        if (!isset($d['totalDays'])) {
            $start = new \DateTimeImmutable($rentedAt);
            $end = new \DateTimeImmutable($dueAt);
            $diff = $start->diff($end);
            $totalDays = (int) ceil(($end->getTimestamp() - $start->getTimestamp()) / 86400);
        } else {
            $totalDays = (int) $d['totalDays'];
        }

        $trx = new self(
            (string) $d['id'],
            (string) $d['userId'],
            (string) $d['mediaId'],
            $rentedAt,
            $dueAt,
            $totalPrice,
            isset($d['returnedAt']) ? (string) $d['returnedAt'] : null,
            (int) ($d['lateFee'] ?? 0),
            $totalDays,
            (int) ($d['totalPaid'] ?? $totalPrice),
            (int) ($d['extensionCount'] ?? 0),
            isset($d['basePriceAtRental']) ? (int) $d['basePriceAtRental'] : null
        );
        $trx->initTimestamps($d['createdAt'] ?? null, $d['updatedAt'] ?? null);
        return $trx;
    }
}
