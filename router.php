<?php
declare(strict_types=1);

/**
 * Router untuk PHP Built-in Server (php -S localhost:8000 router.php).
 * ponytail: dev-only router; di Laragon/Apache pembatasan ditangani .htaccess.
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/');
$path = ltrim(str_replace('\\', '/', $uri), '/');

// 1. Blokir traversal dan berkas tersembunyi
if (str_contains($path, '..') || str_contains('/' . $path, '/.')) {
    http_response_code(403);
    echo '403 Forbidden';
    exit;
}

// 2. Blokir akses ke folder internal dan ekstensi sensitif
$blockedDirs = ['src', 'views', 'data', 'tests', 'docs', '.git'];
$firstSegment = explode('/', $path)[0];

if (
    in_array($firstSegment, $blockedDirs, true)
    || preg_match('/\.(md|json|lock)$/i', $path) === 1
) {
    http_response_code(403);
    echo '403 Forbidden';
    exit;
}

// 3. Teruskan berkas statis di folder assets/
$filePath = __DIR__ . '/' . $path;
if (str_starts_with($path, 'assets/') && is_file($filePath)) {
    return false;
}

// 4. Jalankan front controller
require __DIR__ . '/index.php';
