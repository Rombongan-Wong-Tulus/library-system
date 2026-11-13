<?php
declare(strict_types=1);

namespace App\Factories;

use App\Models\Comic;
use App\Models\Ebook;
use App\Models\Journal;
use App\Models\Media;
use InvalidArgumentException;

final class MediaFactory
{
    /** @param array<string,mixed> $d */
    public static function fromArray(array $d): Media
    {
        $id = (string) $d['id'];
        $title = (string) $d['title'];
        $author = (string) $d['author'];
        $price = (float) $d['basePrice'];
        $stock = (int) $d['stock'];
        $desc = (string) ($d['description'] ?? '');
        $extra = $d['extra'] ?? null;

        return match ($d['type']) {
            'ebook' => new Ebook($id, $title, $author, $price, $stock, $desc, (float) ($extra ?? 0)),
            'comic' => new Comic($id, $title, $author, $price, $stock, $desc, (int) ($extra ?? 1)),
            'journal' => new Journal($id, $title, $author, $price, $stock, $desc, (string) ($extra ?? '-')),
            default => throw new InvalidArgumentException('Tipe media tidak dikenal: ' . $d['type']),
        };
    }
}
