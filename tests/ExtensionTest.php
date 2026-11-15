<?php
declare(strict_types=1);

require __DIR__ . '/../src/autoload.php';

use App\Models\Transaction;
use App\Models\Comic;
use App\Models\Ebook;
use App\Models\Journal;
use App\Models\Member;
use App\Models\PremiumMember;
use App\Services\PriceCalculator;
use App\Exceptions\RentalException;

$fail = 0;

function check(string $name, $actual, $expected): void
{
    global $fail;
    if (is_float($actual) && is_float($expected)) {
        $ok = abs($actual - $expected) < 0.01;
    } else {
        $ok = $actual === $expected;
    }
    echo ($ok ? '[OK]    ' : '[GAGAL] ') . "$name (hasil " . var_export($actual, true) . ", harapan " . var_export($expected, true) . ")\n";
    if (!$ok) {
        $fail++;
    }
}

$priceCalculator = new PriceCalculator();

echo "--- Tes PriceCalculator ---\n";
check('Ebook 3 hari (Member)', $priceCalculator->calculateTotal('ebook', 2000, 3, 'member'), 6000);
check('Komik 7 hari (Member, diskon 15%)', $priceCalculator->calculateTotal('comic', 1500, 7, 'member'), 8925);
check('Komik 7 hari (Premium, 15% then 20%)', $priceCalculator->calculateTotal('comic', 1500, 7, 'premium'), 7140);
check('Jurnal 5 hari (Member, flat)', $priceCalculator->calculateTotal('journal', 5000, 5, 'member'), 5000);
check('Pembulatan 10001 * 7 * 0.85 * 0.80', $priceCalculator->calculateTotal('comic', 10001, 7, 'premium'), 47605); // 10001*7*0.85*0.80 = 47604.76 -> round -> 47605

echo "\n--- Tes Transaction Extension ---\n";
$now = new DateTimeImmutable('2026-11-15 10:00:00');
$rentedAt = $now->modify('-3 days')->format('Y-m-d H:i:s');
$dueAt = $now->modify('+3 days')->format('Y-m-d H:i:s'); // total 6 days

$trx = new Transaction(
    'T001', 'U001', 'M001',
    $rentedAt, $dueAt, 6000,
    null, 0, 6, 6000, 0, 1000
);

// 1. Perpanjang 3 hari (total 9 hari)
$additionalFee = $trx->extend(3, 9000, $now);
check('Additional Fee (6->9 hari)', $additionalFee, 3000);
check('New DueAt', $trx->getDueAt(), $now->modify('+6 days')->format('Y-m-d H:i:s')); // original dueAt was +3 days, +3 extra = +6 days from now (or original +3)
check('Total Days', $trx->getTotalDays(), 9);
check('Extension Count', $trx->getExtensionCount(), 1);
check('Total Paid', $trx->getTotalPaid(), 9000);

// 2. Perpanjang lagi 5 hari (total 14 hari)
$additionalFee = $trx->extend(5, 14000, $now);
check('Additional Fee (9->14 hari)', $additionalFee, 5000);
check('Total Days (final)', $trx->getTotalDays(), 14);

// 3. Perpanjang lagi (total 15 hari) -> Ditolak
try {
    $trx->extend(1, 15000, $now);
    check('Extend > 14 hari', 'Lolos', 'Ditolak');
} catch (RentalException $e) {
    check('Extend > 14 hari', $e->getMessage(), 'Total durasi sewa kumulatif maksimal 14 hari.');
}

// 4. Transaksi terlambat -> Ditolak
$lateTrx = new Transaction(
    'T002', 'U001', 'M001',
    $now->modify('-5 days')->format('Y-m-d H:i:s'),
    $now->modify('-1 day')->format('Y-m-d H:i:s'),
    4000, null, 0, 4, 4000, 0, 1000
);
try {
    $lateTrx->extend(1, 5000, $now);
    check('Extend terlambat', 'Lolos', 'Ditolak');
} catch (RentalException $e) {
    check('Extend terlambat', $e->getMessage(), 'Transaksi sudah terlambat dan tidak bisa diperpanjang.');
}

// 5. Transaksi sudah dikembalikan -> Ditolak
$returnedTrx = new Transaction(
    'T003', 'U001', 'M001',
    $now->modify('-5 days')->format('Y-m-d H:i:s'),
    $now->modify('+2 days')->format('Y-m-d H:i:s'),
    7000, $now->format('Y-m-d H:i:s'), 0, 7, 7000, 0, 1000
);
try {
    $returnedTrx->extend(1, 8000, $now);
    check('Extend dikembalikan', 'Lolos', 'Ditolak');
} catch (RentalException $e) {
    check('Extend dikembalikan', $e->getMessage(), 'Transaksi sudah dikembalikan dan tidak bisa diperpanjang.');
}

// 6. Fallback data lama
$oldData = [
    'id' => 'T004',
    'userId' => 'U001',
    'mediaId' => 'M001',
    'rentedAt' => '2026-11-10 10:00:00',
    'dueAt' => '2026-11-13 10:00:00',
    'totalPrice' => 6000.0,
];
$trxOld = Transaction::fromArray($oldData);
check('Fallback totalDays', $trxOld->getTotalDays(), 3);
check('Fallback totalPaid', $trxOld->getTotalPaid(), 6000);
check('Fallback basePriceAtRental', $trxOld->getBasePriceAtRental(), null);

echo $fail === 0 ? "\nSemua tes lulus.\n" : "\n$fail tes gagal.\n";
exit($fail === 0 ? 0 : 1);
