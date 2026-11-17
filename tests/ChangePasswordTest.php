<?php
declare(strict_types=1);

require __DIR__ . '/../src/autoload.php';

use App\Exceptions\ValidationException;
use App\Models\Member;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Storage\JsonStorage;

$fail = 0;
function check(string $name, mixed $actual, mixed $expected): void
{
    global $fail;
    $ok = $actual === $expected;
    echo ($ok ? '[OK]    ' : '[GAGAL] ') . "$name (hasil " . var_export($actual, true) . ", harapan " . var_export($expected, true) . ")\n";
    if (!$ok) { $fail++; }
}

// --- Setup: buat user dengan password ter-hash di temp JSON ---
$tempFile = sys_get_temp_dir() . '/chpw_' . bin2hex(random_bytes(4)) . '.json';
$hash = password_hash('password123', PASSWORD_DEFAULT);
$seed = [[
    'id'           => 'U1',
    'role'         => 'member',
    'name'         => 'Test User',
    'email'        => 'test@perpus.test',
    'passwordHash' => $hash,
    'createdAt'    => date('Y-m-d H:i:s'),
    'updatedAt'    => date('Y-m-d H:i:s'),
]];
file_put_contents($tempFile, json_encode($seed));

$userRepo = new UserRepository(new JsonStorage($tempFile));
$auth     = new AuthService($userRepo);

// Ambil user dari repo agar objek dibentuk via factory
$user = $userRepo->find('U1');

// --- TES 1: Password lama salah ---
try {
    $auth->changePassword($user, 'wrongpassword', 'newpass123', 'newpass123');
    check('TES 1 password lama salah', 'lolos', 'exception');
} catch (ValidationException $e) {
    check('TES 1 password lama salah', $e->getErrors(), ['Password lama salah.']);
}

// --- TES 2: Password baru terlalu pendek ---
try {
    $auth->changePassword($user, 'password123', 'abc', 'abc');
    check('TES 2 baru terlalu pendek', 'lolos', 'exception');
} catch (ValidationException $e) {
    check('TES 2 baru terlalu pendek', $e->getErrors(), ['Password baru minimal 6 karakter.']);
}

// --- TES 3: Konfirmasi beda ---
try {
    $auth->changePassword($user, 'password123', 'newpass123', 'different123');
    check('TES 3 konfirmasi beda', 'lolos', 'exception');
} catch (ValidationException $e) {
    check('TES 3 konfirmasi beda', $e->getErrors(), ['Konfirmasi password tidak sama.']);
}

// --- TES 4: Baru sama dengan lama ---
try {
    $auth->changePassword($user, 'password123', 'password123', 'password123');
    check('TES 4 baru sama lama', 'lolos', 'exception');
} catch (ValidationException $e) {
    check('TES 4 baru sama lama', $e->getErrors(), ['Password baru harus berbeda dari password lama.']);
}

// --- TES 5: Beberapa error sekaligus (lama salah + baru pendek + konfirmasi beda) ---
try {
    $auth->changePassword($user, 'wrongpassword', 'ab', 'xx');
    check('TES 5 multi-error', 'lolos', 'exception');
} catch (ValidationException $e) {
    $errors = $e->getErrors();
    $ok = count($errors) >= 2;
    echo ($ok ? '[OK]    ' : '[GAGAL] ') . "TES 5 multi-error (" . count($errors) . " errors)\n";
    if (!$ok) { $fail++; }
}

// --- TES 6: Data tidak berubah setelah gagal ---
$userBefore = $userRepo->find('U1');
$canVerifyOld = $userBefore->verifyPassword('password123');
try {
    $auth->changePassword($userBefore, 'wrongpassword', 'newpass123', 'newpass123');
} catch (ValidationException $e) {
    // expected
}
$userAfter = $userRepo->find('U1');
check('TES 6 data tidak berubah', $userAfter->verifyPassword('password123'), true);

// --- TES 7: Sukses - password baru tersimpan ---
$freshUser = $userRepo->find('U1');
try {
    $auth->changePassword($freshUser, 'password123', 'rahasia_baru', 'rahasia_baru');
    // Baca ulang dari repository
    $saved = $userRepo->find('U1');
    check('TES 7 sukses: baru berlaku', $saved->verifyPassword('rahasia_baru'), true);
} catch (ValidationException $e) {
    check('TES 7 sukses', 'exception: ' . implode(', ', $e->getErrors()), 'tidak ada exception');
}

// --- TES 8: Password lama tidak berlaku lagi ---
$saved2 = $userRepo->find('U1');
check('TES 8 lama tidak berlaku', $saved2->verifyPassword('password123'), false);

// --- TES 9: File JSON tidak mengandung teks password asli ---
$json = file_get_contents($tempFile);
$noPlain1 = strpos($json, 'password123') === false;
$noPlain2 = strpos($json, 'rahasia_baru') === false;
check('TES 9 JSON tanpa plaintext', $noPlain1 && $noPlain2, true);

// Bersihkan
@unlink($tempFile);

echo $fail === 0 ? "\nSemua tes lulus.\n" : "\n$fail tes gagal.\n";
exit($fail === 0 ? 0 : 1);
