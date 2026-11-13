<?php
declare(strict_types=1);

require __DIR__ . '/src/autoload.php';

use App\Core\Application;

(new Application(__DIR__))->run();
