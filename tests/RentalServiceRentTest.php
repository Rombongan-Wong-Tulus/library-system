<?php
declare(strict_types=1);

require __DIR__ . '/../src/autoload.php';

use App\Exceptions\RentalException;
use App\Models\Admin;
use App\Models\Comic;
use App\Models\Ebook;
use App\Models\Journal;
use App\Models\Member;
use App\Models\PremiumMember;
use App\Repositories\MediaRepository;
use App\Repositories\TransactionRepository;
use App\Services\PriceCalculator;
use App\Services\RentalService;
use App\Storage\JsonStorage;

$fail = 0;
function check(string $name, $actual, $expected): void
{
    global $fail;
    $ok = $actual === $expected;
    echo ($ok ? '[OK]    ' : '[GAGAL] ') . "$name (hasil " . var_export($actual, true) . ", harapan " . var_export($expected, true) . ")\n";
    if (!$ok) { $fail++; }
}

$mediaFile = sys_get_temp_dir() . '/rent_media_' . bin2hex(random_bytes(4)) . '.json';
$trxFile = sys_get_temp_dir() . '/rent_trx_' . bin2hex(random_bytes(4)) . '.json';
file_put_contents($mediaFile, '[]');
file_put_contents($trxFile, '[]');
$mediaRepo = new MediaRepository(new JsonStorage($mediaFile));
$trxRepo = new TransactionRepository(new JsonStorage($trxFile));
$service = new RentalService($mediaRepo, $trxRepo, new PriceCalculator());
$member = new Member('U1', 'Member', 'm@test.id', 'hash');
$premium = new PremiumMember('U2', 'Premium', 'p@test.id', 'hash');
$admin = new Admin('U3', 'Admin', 'a@test.id', 'hash');

$cases = [
    ['M1', new Comic('M1', 'Comic Premium', 'A', 1500.0, 1), $premium, 7, 7140],
    ['M2', new Comic('M2', 'Comic Member', 'A', 1500.0, 1), $member, 7, 8925],
    ['M3', new Ebook('M3', 'Ebook Member', 'A', 2000.0, 1), $member, 3, 6000],
    ['M4', new Journal('M4', 'Journal Premium', 'A', 5000.0, 1), $premium, 5, 4000],
];
foreach ($cases as [$id, $media, $user, $days, $expected]) {
    $mediaRepo->save($media);
    $trx = $service->rent($user, $id, $days);
    check("rent $id totalPrice", $trx->getTotalPrice(), $expected);
    check("rent $id basePriceAtRental", $trx->getBasePriceAtRental(), (int) $media->getBasePrice());
    check("rent $id totalDays", $trx->getTotalDays(), $days);
    check("rent $id totalPaid", $trx->getTotalPaid(), $expected);
    check("rent $id extensionCount", $trx->getExtensionCount(), 0);
    check("rent $id stock", $mediaRepo->find($id)->getStock(), 0);
}

try { $service->rent($member, 'M1', 0); check('rent 0 hari', 'Lolos', 'Ditolak'); }
catch (RentalException $e) { check('rent 0 hari', $e->getMessage(), 'Lama sewa harus 1-14 hari.'); }
try { $service->rent($member, 'M1', 15); check('rent 15 hari', 'Lolos', 'Ditolak'); }
catch (RentalException $e) { check('rent 15 hari', $e->getMessage(), 'Lama sewa harus 1-14 hari.'); }
try { $service->rent($admin, 'M1', 1); check('rent admin', 'Lolos', 'Ditolak'); }
catch (RentalException $e) { check('rent admin', $e->getMessage(), 'Admin tidak dapat menyewa media.'); }

@unlink($mediaFile);
@unlink($trxFile);
echo $fail === 0 ? "\nSemua tes lulus.\n" : "\n$fail tes gagal.\n";
exit($fail === 0 ? 0 : 1);
