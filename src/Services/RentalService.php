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
        private TransactionRepository $transactions,
        private PriceCalculator $priceCalculator
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

        // Gunakan PriceCalculator terpusat
        $basePriceAtRental = (int) $media->getBasePrice();
        $totalPrice = $this->priceCalculator->calculateTotal(
            $media->getType()->value,
            $basePriceAtRental,
            $days,
            $user->getRole()->value
        );

        $now = new DateTimeImmutable();
        $trx = new Transaction(
            $this->transactions->nextId(),
            $user->getId(),
            $media->getId(),
            $now->format('Y-m-d H:i:s'),
            $now->modify("+{$days} days")->format('Y-m-d H:i:s'),
            $totalPrice,
            null, // returnedAt
            0,    // lateFee
            $days,
            $totalPrice, // totalPaid awal sama dengan totalPrice
            0,    // extensionCount
            $basePriceAtRental
        );

        $media->decreaseStock();
        $this->media->save($media);
        $this->transactions->save($trx);

        return $trx;
    }

    /**
     * Perpanjang masa sewa media.
     *
     * @param User $user Pengguna yang melakukan perpanjangan.
     * @param string $transactionId ID transaksi yang akan diperpanjang.
     * @param int $extraDays Jumlah hari tambahan (1-14).
     * @param DateTimeImmutable $now Waktu saat ini.
     * @return array{trx: Transaction, additionalFee: int} Objek transaksi yang telah diperbarui dan biaya tambahan.
     * @throws RentalException Jika validasi gagal.
     */
    public function extendRental(User $user, string $transactionId, int $extraDays, DateTimeImmutable $now): array
    {
        $trx = $this->transactions->find($transactionId);
        if ($trx === null || $trx->getUserId() !== $user->getId()) {
            throw new RentalException('Transaksi tidak ditemukan.');
        }

        // Validasi status transaksi & durasi perpanjangan dasar
        $trx->assertExtendable($extraDays, $now);

        // Ambil media untuk mendapatkan tipenya
        $media = $this->media->find($trx->getMediaId());
        if ($media === null) {
            throw new RentalException('Data media tidak ditemukan.');
        }

        // Hitung harga total baru
        $newTotalDays = $trx->getTotalDays() + $extraDays;

        // snapshot harga dasar saat transaksi dibuat
        $basePrice = $trx->getBasePriceAtRental();
        if ($basePrice === null) {
            throw new RentalException('Data harga transaksi ini tidak lengkap sehingga tidak bisa diperpanjang. Hubungi admin.');
        }

        $newPrice = $this->priceCalculator->calculateTotal(
            $media->getType()->value,
            $basePrice,
            $newTotalDays,
            $user->getRole()->value
        );

        // Lakukan perpanjangan pada objek Transaction (validasi internal dilakukan di sana)
        $additionalFee = $trx->extend($extraDays, $newPrice, $now);

        // Simpan transaksi
        $this->transactions->save($trx);

        return ['trx' => $trx, 'additionalFee' => $additionalFee];
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
