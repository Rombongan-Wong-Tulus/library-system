<?php
declare(strict_types=1);

require dirname(__DIR__) . '/src/autoload.php';

use App\Core\Application;

(new Application(dirname(__DIR__)))->run();
