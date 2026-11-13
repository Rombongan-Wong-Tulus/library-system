<?php
declare(strict_types=1);

namespace App\Services;

use App\Exceptions\RentalException;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\MediaRepository;
use App\Repositories\TransactionRepository;
use DateTimeImmutable;

class RentalService
{
    public const MIN_DAYS = 1;
    public const MAX_DAYS = 14;

    public function __construct(
        private MediaRepository $media,
        private TransactionRepository $transactions
    ) {
    }

    public function rent(User $user, string $mediaId, int $days): Transaction
    {
        if ($user->isAdmin()) {
            throw new RentalException('Admin tidak dapat menyewa media.');
        }
        if ($days < self::MIN_DAYS || $days > self::MAX_DAYS) {
            throw new RentalException(sprintf('Lama sewa harus %d-%d hari.', self::MIN_DAYS, self::MAX_DAYS));
        }

        $media = $this->media->find($mediaId);
        if ($media === null) {
            throw new RentalException('Media tidak ditemukan.');
        }
        if (!$media->isAvailable()) {
            throw new RentalException('Stok "' . $media->getTitle() . '" sedang habis.');
        }

        $active = array_filter(
            $this->transactions->byUser($user->getId()),
            static fn(Transaction $t): bool => $t->isActive()
        );
        if (count($active) >= $user->getMaxLoans()) {
            throw new RentalException('Batas pinjaman aktif (' . $user->getMaxLoans() . ') sudah tercapai.');
        }
        foreach ($active as $t) {
            if ($t->getMediaId() === $mediaId) {
                throw new RentalException('Kamu masih menyewa media ini.');
            }
        }

        // Polymorphism: harga dihitung oleh subclass Media, diskon oleh subclass User.
        $price = $media->getRentalPrice($days) * (1 - $user->getDiscount());

        $now = new DateTimeImmutable();
        $trx = new Transaction(
            $this->transactions->nextId(),
            $user->getId(),
            $media->getId(),
            $now->format('Y-m-d H:i:s'),
            $now->modify("+{$days} days")->format('Y-m-d H:i:s'),
            round($price, 2)
        );

        $media->decreaseStock();
        $this->media->save($media);
        $this->transactions->save($trx);

        return $trx;
    }

    public function returnMedia(User $user, string $transactionId): Transaction
    {
        $trx = $this->transactions->find($transactionId);
        if ($trx === null || $trx->getUserId() !== $user->getId()) {
            throw new RentalException('Transaksi tidak ditemukan.');
        }
        if (!$trx->isActive()) {
            throw new RentalException('Media ini sudah dikembalikan.');
        }

        $trx->markReturned();
        $this->transactions->save($trx);

        $media = $this->media->find($trx->getMediaId());
        if ($media !== null) {
            $media->increaseStock();
            $this->media->save($media);
        }

        return $trx;
    }
}
