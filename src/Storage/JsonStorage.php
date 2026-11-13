<?php
declare(strict_types=1);

namespace App\Storage;

use App\Contracts\StorageInterface;
use RuntimeException;

/**
 * Membaca dan menulis data JSON secara atomik dengan locking (flock).
 */
class JsonStorage implements StorageInterface
{
    public function __construct(private string $filePath)
    {
        if (!is_file($filePath)) {
            @mkdir(dirname($filePath), 0775, true);
            $seedPath = dirname($filePath) . '/' . pathinfo($filePath, PATHINFO_FILENAME) . '.seed.json';
            if (is_file($seedPath)) {
                copy($seedPath, $filePath);
            } else {
                file_put_contents($filePath, '[]');
            }
        }
    }

    /** @return array<int, array<string, mixed>> */
    public function read(): array
    {
        if (!is_file($this->filePath)) {
            return [];
        }

        $fp = fopen($this->filePath, 'rb');
        if ($fp === false) {
            return [];
        }

        flock($fp, LOCK_SH);
        $raw = stream_get_contents($fp);
        flock($fp, LOCK_UN);
        fclose($fp);

        $data = json_decode($raw === false || $raw === '' ? '[]' : $raw, true);
        return is_array($data) ? $data : [];
    }

    /**
     * Tulis data secara atomik: tulis ke file temporary di direktori yang sama, lalu rename.
     * @param array<int, array<string, mixed>> $data
     */
    public function write(array $data): void
    {
        $json = json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            throw new RuntimeException('Gagal melakukan serialisasi data JSON.');
        }

        $dir = dirname($this->filePath);
        $tmpFile = $dir . '/~' . basename($this->filePath) . '.' . bin2hex(random_bytes(4)) . '.tmp';

        $fp = fopen($tmpFile, 'wb');
        if ($fp === false) {
            throw new RuntimeException('Gagal membuka file sementara untuk penulisan atomik.');
        }

        flock($fp, LOCK_EX);
        fwrite($fp, $json);
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);

        if (!rename($tmpFile, $this->filePath)) {
            @unlink($tmpFile);
            throw new RuntimeException('Gagal memindahkan file sementara ke target: ' . basename($this->filePath));
        }
    }

    /**
     * Baca-ubah-tulis dalam satu lock file eksklusif untuk menghindari race condition.
     * @param callable(array<int, array<string, mixed>>): array<int, array<string, mixed>> $callback
     */
    public function transaction(callable $callback): void
    {
        $lockFile = $this->filePath . '.lock';
        $lockFp = fopen($lockFile, 'c+');
        if ($lockFp === false) {
            throw new RuntimeException('Gagal membuka lock file: ' . basename($lockFile));
        }

        flock($lockFp, LOCK_EX);
        try {
            $data = $this->read();
            $updated = $callback($data);
            $this->write($updated);
        } finally {
            flock($lockFp, LOCK_UN);
            fclose($lockFp);
        }
    }
}
