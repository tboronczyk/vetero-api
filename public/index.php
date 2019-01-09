<?php
declare(strict_types=1);

chdir(__DIR__ . '/..');
require_once 'vendor/autoload.php';

(new Dotenv\Dotenv('.'))->load();
(require_once 'dependencies.php')->App->run();
