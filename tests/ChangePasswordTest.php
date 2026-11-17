<?php
declare(strict_types=1);

require __DIR__ . '/../src/autoload.php';

use App\Exceptions\ValidationException;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Storage\JsonStorage;

$fail = 0;
function check(string $name, mixed $actual, mixed $expected): void
{
    global $fail;
    $ok = $actual === $expected;
    echo ($ok ? '[OK]    ' : '[GAGAL] ') . "$name (hasil " . var_export($actual, true) . ", harapan " . var_export($expected, true) . ")\n";
    if (!$ok) {
        $fail++;
    }
}

$tempFile = sys_get_temp_dir() . '/chpw_' . bin2hex(random_bytes(4)) . '.json';
$hash = password_hash('password123', PASSWORD_DEFAULT);
$seed = [[
    'id' => 'U1',
    'role' => 'member',
    'name' => 'Test User',
    'email' => 'test@perpus.test',
    'passwordHash' => $hash,
    'createdAt' => date('Y-m-d H:i:s'),
    'updatedAt' => date('Y-m-d H:i:s'),
]];
file_put_contents($tempFile, json_encode($seed));

$userRepo = new UserRepository(new JsonStorage($tempFile));
$auth = new AuthService($userRepo);
$user = $userRepo->find('U1');

try {
    $auth->changePassword($user, 'wrongpassword', 'newpass123', 'newpass123');
    check('TES 1 password lama salah', 'lolos', 'exception');
} catch (ValidationException $e) {
    check('TES 1 password lama salah', $e->getErrors(), ['Password lama salah.']);
}

try {
    $auth->changePassword($user, 'password123', 'abc', 'abc');
    check('TES 2 baru terlalu pendek', 'lolos', 'exception');
} catch (ValidationException $e) {
    check('TES 2 baru terlalu pendek', $e->getErrors(), ['Password baru minimal 6 karakter.']);
}

try {
    $auth->changePassword($user, 'password123', 'newpass123', 'different123');
    check('TES 3 konfirmasi beda', 'lolos', 'exception');
} catch (ValidationException $e) {
    check('TES 3 konfirmasi beda', $e->getErrors(), ['Konfirmasi password tidak sama.']);
}

try {
    $auth->changePassword($user, 'password123', 'password123', 'password123');
    check('TES 4 baru sama lama', 'lolos', 'exception');
} catch (ValidationException $e) {
    check('TES 4 baru sama lama', $e->getErrors(), ['Password baru harus berbeda dari password lama.']);
}

try {
    $auth->changePassword($user, 'wrongpassword', 'ab', 'xx');
    check('TES 5 multi-error', 'lolos', 'exception');
} catch (ValidationException $e) {
    $errors = $e->getErrors();
    $ok = count($errors) >= 2;
    echo ($ok ? '[OK]    ' : '[GAGAL] ') . 'TES 5 multi-error (' . count($errors) . " errors)\n";
    if (!$ok) {
        $fail++;
    }
}

$userBefore = $userRepo->find('U1');
try {
    $auth->changePassword($userBefore, 'wrongpassword', 'newpass123', 'newpass123');
} catch (ValidationException) {
}
$userAfter = $userRepo->find('U1');
check('TES 6 data tidak berubah', $userAfter->verifyPassword('password123'), true);

$freshUser = $userRepo->find('U1');
try {
    $auth->changePassword($freshUser, 'password123', 'rahasia_baru', 'rahasia_baru');
    $saved = $userRepo->find('U1');
    check('TES 7 sukses: baru berlaku', $saved->verifyPassword('rahasia_baru'), true);
} catch (ValidationException $e) {
    check('TES 7 sukses', 'exception: ' . implode(', ', $e->getErrors()), 'tidak ada exception');
}

$saved2 = $userRepo->find('U1');
check('TES 8 lama tidak berlaku', $saved2->verifyPassword('password123'), false);

$json = file_get_contents($tempFile);
$noPlain1 = strpos($json, 'password123') === false;
$noPlain2 = strpos($json, 'rahasia_baru') === false;
check('TES 9 JSON tanpa plaintext', $noPlain1 && $noPlain2, true);

@unlink($tempFile);

echo $fail === 0 ? "\nSemua tes lulus.\n" : "\n$fail tes gagal.\n";
exit($fail === 0 ? 0 : 1);
