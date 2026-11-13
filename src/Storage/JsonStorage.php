<?php
declare(strict_types=1);

namespace App\Storage;

use RuntimeException;

/** Membaca/menulis array ke file JSON dengan file locking. */
class JsonStorage
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

    /** @return array<int,array<string,mixed>> */
    public function read(): array
    {
        $raw = file_get_contents($this->filePath);
        $data = json_decode($raw === false ? '[]' : $raw, true);
        return is_array($data) ? $data : [];
    }

    /** @param array<int,array<string,mixed>> $data */
    public function write(array $data): void
    {
        $json = json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($json === false || file_put_contents($this->filePath, $json, LOCK_EX) === false) {
            throw new RuntimeException('Gagal menyimpan data ke ' . basename($this->filePath));
        }
    }
}
