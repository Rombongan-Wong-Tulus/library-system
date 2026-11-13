<?php
declare(strict_types=1);

namespace App\Enums;

enum MediaType: string
{
    case EBOOK = 'ebook';
    case COMIC = 'comic';
    case JOURNAL = 'journal';

    public function label(): string
    {
        return match ($this) {
            self::EBOOK => 'E-book',
            self::COMIC => 'Komik',
            self::JOURNAL => 'Jurnal',
        };
    }

    public function extraLabel(): string
    {
        return match ($this) {
            self::EBOOK => 'Ukuran file (MB)',
            self::COMIC => 'Nomor volume',
            self::JOURNAL => 'Penerbit',
        };
    }

    public function rentHint(): string
    {
        return match ($this) {
            self::EBOOK => 'Harga per hari',
            self::COMIC => 'Per hari, diskon 15% mulai 7 hari',
            self::JOURNAL => 'Tarif flat berapa pun lama sewa',
        };
    }
}
