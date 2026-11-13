<?php
declare(strict_types=1);

namespace App\Factories;

use App\Enums\MediaType;
use App\Models\Comic;
use App\Models\Ebook;
use App\Models\Journal;
use App\Models\Media;
use InvalidArgumentException;
use ValueError;

final class MediaFactory
{
    /** @param array<string, mixed> $d */
    public static function fromArray(array $d): Media
    {
        $id = (string) $d['id'];
        $title = (string) $d['title'];
        $author = (string) $d['author'];
        $price = (float) $d['basePrice'];
        $stock = (int) $d['stock'];
        $desc = (string) ($d['description'] ?? '');
        $extra = $d['extra'] ?? null;

        try {
            $type = $d['type'] instanceof MediaType ? $d['type'] : MediaType::from((string) $d['type']);
        } catch (ValueError) {
            throw new InvalidArgumentException('Tipe media tidak dikenal: ' . ($d['type'] ?? ''));
        }

        $media = match ($type) {
            MediaType::EBOOK => new Ebook($id, $title, $author, $price, $stock, $desc, (float) ($extra ?? 0)),
            MediaType::COMIC => new Comic($id, $title, $author, $price, $stock, $desc, (int) ($extra ?? 1)),
            MediaType::JOURNAL => new Journal($id, $title, $author, $price, $stock, $desc, (string) ($extra ?? '-')),
        };

        $media->initTimestamps($d['createdAt'] ?? null, $d['updatedAt'] ?? null);

        return $media;
    }
}
