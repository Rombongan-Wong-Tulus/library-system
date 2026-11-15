<?php
declare(strict_types=1);

require __DIR__ . '/../src/autoload.php';

use App\Models\Ebook;
use App\Models\Member;
use App\Models\Transaction;
use App\Repositories\MediaRepository;
use App\Repositories\TransactionRepository;
use App\Services\PriceCalculator;
use App\Services\RentalService;
use App\Storage\JsonStorage;
use App\Exceptions\RentalException;

$fail = 0;

function check(string $name, $actual, $expected): void
{
    global $fail;
    $ok = $actual === $expected;
    echo ($ok ? '[OK]    ' : '[GAGAL] ') . "$name (hasil " . var_export($actual, true) . ", harapan " . var_export($expected, true) . ")\n";
    if (!$ok) {
        $fail++;
    }
}

// Setup temporary storage files
$tmpDir = sys_get_temp_dir();
$mediaFile = $tmpDir . '/media_' . bin2hex(random_bytes(4)) . '.json';
$trxFile = $tmpDir . '/trx_' . bin2hex(random_bytes(4)) . '.json';

file_put_contents($mediaFile, '[]');
file_put_contents($trxFile, '[]');

$mediaRepo = new MediaRepository(new JsonStorage($mediaFile));
$trxRepo = new TransactionRepository(new JsonStorage($trxFile));
$priceCalculator = new PriceCalculator();
$rentalService = new RentalService($mediaRepo, $trxRepo, $priceCalculator);

$now = new DateTimeImmutable('2026-11-15 10:00:00');

// Seed media & user
$media = new Ebook('M001', 'Test Ebook', 'Author', 2000.0, 5);
$mediaRepo->save($media);

$user = new Member('U001', 'Member', 'member@test.com', 'hash');

// Create active transaction
$trx = new Transaction(
    'T001', 'U001', 'M001',
    $now->modify('-2 days')->format('Y-m-d H:i:s'),
    $now->modify('+2 days')->format('Y-m-d H:i:s'),
    4000, null, 0, 4, 4000, 0, 2000
);
$trxRepo->save($trx);

echo "--- Tes RentalService::extendRental ---\n";

// a. Perpanjang transaksi aktif belum terlambat
$result = $rentalService->extendRental($user, 'T001', 3, $now);
check('a. Perpanjang aktif belum terlambat (dueAt)', $result['trx']->getDueAt(), $now->modify('+5 days')->format('Y-m-d H:i:s'));
check('a. Perpanjang aktif belum terlambat (totalDays)', $result['trx']->getTotalDays(), 7);
check('a. Perpanjang aktif belum terlambat (totalPaid)', $result['trx']->getTotalPaid(), 14000);

// b. Transaksi terlambat ditolak
$lateTrx = new Transaction(
    'T002', 'U001', 'M001',
    $now->modify('-5 days')->format('Y-m-d H:i:s'),
    $now->modify('-1 day')->format('Y-m-d H:i:s'),
    4000, null, 0, 4, 4000, 0, 2000
);
$trxRepo->save($lateTrx);
try {
    $rentalService->extendRental($user, 'T002', 1, $now);
    check('b. Transaksi terlambat ditolak', 'Lolos', 'Ditolak');
} catch (RentalException $e) {
    check('b. Transaksi terlambat ditolak', $e->getMessage(), 'Transaksi sudah terlambat dan tidak bisa diperpanjang.');
}

// c. Transaksi sudah dikembalikan ditolak (walaupun totalDays sudah 14)
$returnedTrx = new Transaction(
    'T003', 'U001', 'M001',
    $now->modify('-5 days')->format('Y-m-d H:i:s'),
    $now->modify('+2 days')->format('Y-m-d H:i:s'),
    14000, $now->format('Y-m-d H:i:s'), 0, 14, 14000, 0, 2000
);
$trxRepo->save($returnedTrx);
try {
    $rentalService->extendRental($user, 'T003', 1, $now);
    check('c. Transaksi dikembalikan ditolak', 'Lolos', 'Ditolak');
} catch (RentalException $e) {
    check('c. Transaksi dikembalikan ditolak', $e->getMessage(), 'Transaksi sudah dikembalikan dan tidak bisa diperpanjang.');
}

// d. Total tepat 14 diizinkan; 15 ditolak
// T001 saat ini totalDays = 7. Extend 7 lagi -> total 14
$result14 = $rentalService->extendRental($user, 'T001', 7, $now);
check('d. Total tepat 14 hari diizinkan', $result14['trx']->getTotalDays(), 14);

try {
    $rentalService->extendRental($user, 'T001', 1, $now);
    check('d. Total 15 hari ditolak', 'Lolos', 'Ditolak');
} catch (RentalException $e) {
    check('d. Total 15 hari ditolak', $e->getMessage(), 'Total durasi sewa kumulatif maksimal 14 hari.');
}

// e. Transaksi milik user lain ditolak
$otherUserTrx = new Transaction(
    'T005', 'U999', 'M001',
    $now->format('Y-m-d H:i:s'),
    $now->modify('+2 days')->format('Y-m-d H:i:s'),
    2000, null, 0, 2, 2000, 0, 2000
);
$trxRepo->save($otherUserTrx);
try {
    $rentalService->extendRental($user, 'T005', 1, $now);
    check('e. Transaksi milik user lain ditolak', 'Lolos', 'Ditolak');
} catch (RentalException $e) {
    check('e. Transaksi milik user lain ditolak', $e->getMessage(), 'Transaksi tidak ditemukan.');
}

// f. basePriceAtRental null ditolak dengan pesan P5
$nullBaseTrx = new Transaction(
    'T006', 'U001', 'M001',
    $now->format('Y-m-d H:i:s'),
    $now->modify('+2 days')->format('Y-m-d H:i:s'),
    2000, null, 0, 2, 2000, 0, null
);
$trxRepo->save($nullBaseTrx);
try {
    $rentalService->extendRental($user, 'T006', 1, $now);
    check('f. basePriceAtRental null ditolak', 'Lolos', 'Ditolak');
} catch (RentalException $e) {
    check('f. basePriceAtRental null ditolak', $e->getMessage(), 'Data harga transaksi ini tidak lengkap sehingga tidak bisa diperpanjang. Hubungi admin.');
}

// g. Biaya tambahan nol saat harga baru lebih rendah; totalPaid tidak turun
// (Meskipun secara normal harga naik seiring hari, kita uji properti max logic atau skenario terkait)
// Di sini kita buat transaksi dengan totalPaid tinggi, misal totalPaid 20000, lalu extend dengan harga baru lebih rendah atau sama
$highPaidTrx = new Transaction(
    'T007', 'U001', 'M001',
    $now->format('Y-m-d H:i:s'),
    $now->modify('+2 days')->format('Y-m-d H:i:s'),
    10000, null, 0, 2, 20000, 0, 2000
);
$trxRepo->save($highPaidTrx);
$resultFee = $rentalService->extendRental($user, 'T007', 1, $now); // newPrice untuk 3 hari = 6000
check('g. Biaya tambahan nol saat harga baru lebih rendah', $resultFee['additionalFee'], 0);
check('g. Total Paid tidak turun', $resultFee['trx']->getTotalPaid(), 20000);

// h. Perpanjangan berulang 7+3+4 = 14 diizinkan, berikutnya ditolak
$multiTrx = new Transaction(
    'T008', 'U001', 'M001',
    $now->format('Y-m-d H:i:s'),
    $now->modify('+7 days')->format('Y-m-d H:i:s'),
    14000, null, 0, 7, 14000, 0, 2000
);
$trxRepo->save($multiTrx);
$r1 = $rentalService->extendRental($user, 'T008', 3, $now); // 7 + 3 = 10
$r2 = $rentalService->extendRental($user, 'T008', 4, $now); // 10 + 4 = 14
check('h. Perpanjangan berulang 7+3+4 = 14', $r2['trx']->getTotalDays(), 14);
try {
    $rentalService->extendRental($user, 'T008', 1, $now);
    check('h. Perpanjangan berikutnya ditolak', 'Lolos', 'Ditolak');
} catch (RentalException $e) {
    check('h. Perpanjangan berikutnya ditolak', $e->getMessage(), 'Total durasi sewa kumulatif maksimal 14 hari.');
}

// Cleanup temp files
@unlink($mediaFile);
@unlink($trxFile);

echo $fail === 0 ? "\nSemua tes lulus.\n" : "\n$fail tes gagal.\n";
exit($fail === 0 ? 0 : 1);
