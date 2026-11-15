<?php
declare(strict_types=1);

namespace App\Services;

use App\Enums\MediaType;
use App\Enums\Role;

class PriceCalculator
{
    /**
     * Menghitung total harga sewa berdasarkan tipe media, harga dasar, durasi, dan peran pengguna.
     *
     * @param string $mediaType Tipe media (dari App\Enums\MediaType)
     * @param int $basePrice Harga dasar media saat disewa (integer rupiah)
     * @param int $days Durasi sewa dalam hari
     * @param string $role Peran pengguna (dari App\Enums\Role)
     * @return int Total harga sewa (integer rupiah)
     */
    public function calculateTotal(string $mediaType, int $basePrice, int $days, string $role): int
    {
        $price = (float) $basePrice;

        // 1. Hitung harga berdasarkan tipe media
        switch (MediaType::from($mediaType)) {
            case MediaType::EBOOK:
                $price *= $days;
                break;
            case MediaType::COMIC:
                $price *= $days;
                if ($days >= 7) {
                    $price *= (1 - 0.15); // Diskon 15% untuk komik >= 7 hari
                }
                break;
            case MediaType::JOURNAL:
                // Harga flat, tidak bergantung durasi
                break;
        }

        // 2. Terapkan diskon role secara berurutan
        switch (Role::from($role)) {
            case Role::PREMIUM:
                $price *= (1 - 0.20); // Diskon 20% untuk Premium
                break;
            case Role::MEMBER:
            case Role::ADMIN: // Admin tidak menyewa, tapi untuk konsistensi di sini 0%
            default:
                break;
        }

        return $this->roundRupiah($price);
    }

    /**
     * Membulatkan nominal rupiah ke integer terdekat.
     *
     * @param float $amount
     * @return int
     */
    public function roundRupiah(float $amount): int
    {
        return (int) round($amount);
    }
}
