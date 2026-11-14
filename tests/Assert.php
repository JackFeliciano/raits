<?php

declare(strict_types=1);

namespace Raits\Tests;

/**
 * The handful of assertions the suite needs. A failure throws with the message
 * the test wrote, so the runner can print a line that names the expectation.
 */
final class Assert
{
    public static function same(mixed $expected, mixed $actual, string $what = ''): void
    {
        if ($expected !== $actual) {
            throw new \RuntimeException(
                ($what !== '' ? $what . ': ' : '') . 'expected ' . var_export($expected, true)
                . ' got ' . var_export($actual, true)
            );
        }
    }

    public static function true(bool $value, string $what): void
    {
        if (!$value) {
            throw new \RuntimeException($what);
        }
    }

    public static function contains(string $needle, string $haystack): void
    {
        if (!str_contains($haystack, $needle)) {
            throw new \RuntimeException('the message does not carry ' . var_export($needle, true));
        }
    }
}
