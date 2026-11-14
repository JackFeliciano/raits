<?php

declare(strict_types=1);

namespace Raits\Tests;

require __DIR__ . '/../autoload.php';

$files = glob(__DIR__ . '/*Test.php') ?: [];
sort($files);
$status = 0;
foreach ($files as $file) {
    echo basename($file, '.php') . "\n";
    $code = Runner::run($file);
    if ($code !== 0) {
        $status = $code;
    }
}

exit($status);
