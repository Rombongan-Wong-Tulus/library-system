<?php
declare(strict_types=1);

namespace App\Contracts;

/**
 * Kontrak untuk mekanisme penyimpanan data.
 */
interface StorageInterface
{
    /** @return array<int, array<string, mixed>> */
    public function read(): array;

    /** @param array<int, array<string, mixed>> $data */
    public function write(array $data): void;

    /**
     * Baca-ubah-tulis dalam satu transaksi dengan file lock.
     * @param callable(array<int, array<string, mixed>>): array<int, array<string, mixed>> $callback
     */
    public function transaction(callable $callback): void;
}
