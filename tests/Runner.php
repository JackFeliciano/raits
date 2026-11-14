<?php

declare(strict_types=1);

namespace Raits\Tests;

/**
 * Reads a test file and runs every case in it. A test file returns a list of
 * [name, closure] pairs, and the closure throws when its expectation fails.
 */
final class Runner
{
    public static function run(string $file): int
    {
        $cases = require $file;
        $passed = 0;
        $failed = 0;
        foreach ($cases as [$name, $case]) {
            try {
                $case();
                $passed++;
                echo '  ok   ' . $name . "\n";
            } catch (\Throwable $e) {
                $failed++;
                echo '  FAIL ' . $name . "\n         " . $e->getMessage() . "\n";
            }
        }
        echo "\n" . $passed . ' passed, ' . $failed . " failed\n";

        return $failed === 0 ? 0 : 1;
    }
}
