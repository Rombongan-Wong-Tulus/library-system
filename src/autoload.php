<?php
declare(strict_types=1);

/**
 * PSR-4 autoloader sederhana (tanpa Composer).
 * Namespace App\Models\Ebook  ->  src/Models/Ebook.php
 */
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = __DIR__ . '/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require $file;
    }
});
