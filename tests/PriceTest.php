<?php
declare(strict_types=1);

require __DIR__ . '/../src/autoload.php';

use App\Models\Comic;
use App\Models\Ebook;
use App\Models\Journal;
use App\Models\Member;
use App\Models\PremiumMember;
use App\Models\Transaction;

$fail = 0;

function check(string $name, mixed $actual, mixed $expected): void
{
    global $fail;
    $ok = is_numeric($actual) && is_numeric($expected)
        ? abs((float) $actual - (float) $expected) < 0.01
        : $actual === $expected;

    echo ($ok ? '[OK]    ' : '[GAGAL] ') . "$name (hasil " . var_export($actual, true) . ", harapan " . var_export($expected, true) . ")\n";
    if (!$ok) {
        $fail++;
    }
}

// 1. Tes Harga Media (Polimorfisme)
$ebook = new Ebook('t1', 'Tes', 'X', 2000.0, 1);
check('Ebook 3 hari', $ebook->getRentalPrice(3), 6000);

$comic = new Comic('t2', 'Tes', 'X', 1500.0, 1);
check('Komik 6 hari (tanpa diskon)', $comic->getRentalPrice(6), 9000);
check('Komik 7 hari (diskon 15%)', $comic->getRentalPrice(7), 8925);

$journal = new Journal('t3', 'Tes', 'X', 5000.0, 1);
check('Jurnal 1 hari', $journal->getRentalPrice(1), 5000);
check('Jurnal 14 hari', $journal->getRentalPrice(14), 5000);

// 2. Tes Role & Diskon
$member = new Member('u1', 'A', 'a@a.id', 'x');
$premium = new PremiumMember('u2', 'B', 'b@b.id', 'x');
check('Member maks pinjam', $member->getMaxLoans(), 3);
check('Member diskon', $member->getDiscount(), 0.0);
check('Premium maks pinjam', $premium->getMaxLoans(), 5);
check('Premium diskon', $premium->getDiscount(), 0.20);

// 3. Tes Denda Keterlambatan
$pastDue = (new DateTimeImmutable('-2 days'))->format('Y-m-d H:i:s');
$rentedAt = (new DateTimeImmutable('-5 days'))->format('Y-m-d H:i:s');
$lateTrx = new Transaction('t_late', 'u1', 't1', $rentedAt, $pastDue, 6000.0);
check('Status transaksi sebelum dikembalikan (overdue)', $lateTrx->isOverdue(), true);
$lateTrx->markReturned();
check('Status transaksi setelah dikembalikan (aktif?)', $lateTrx->isActive(), false);
check('Denda keterlambatan 2 hari (Rp 1.000/hari)', $lateTrx->getLateFee(), 2000.0);

// 4. Tes Enum (bila sudah diimplementasikan)
if (enum_exists('App\\Enums\\MediaType')) {
    check('Enum MediaType ebook', \App\Enums\MediaType::EBOOK->value, 'ebook');
    check('Enum MediaType comic', \App\Enums\MediaType::COMIC->value, 'comic');
    check('Enum MediaType journal', \App\Enums\MediaType::JOURNAL->value, 'journal');
}
if (enum_exists('App\\Enums\\Role')) {
    check('Enum Role admin', \App\Enums\Role::ADMIN->value, 'admin');
    check('Enum Role member', \App\Enums\Role::MEMBER->value, 'member');
    check('Enum Role premium', \App\Enums\Role::PREMIUM->value, 'premium');
}

// 5. Tes Trait Timestampable (bila sudah diimplementasikan)
if (trait_exists('App\\Traits\\Timestampable')) {
    $mediaTraits = class_uses(\App\Models\Media::class);
    check('Media uses Timestampable', isset($mediaTraits['App\\Traits\\Timestampable']), true);
    check('Ebook has getCreatedAt', method_exists($ebook, 'getCreatedAt'), true);
    check('Media __toString()', (string) $ebook !== '', true);
    check('User __toString()', (string) $member !== '', true);
}

echo $fail === 0 ? "\nSemua tes lulus.\n" : "\n$fail tes gagal.\n";
exit($fail === 0 ? 0 : 1);
