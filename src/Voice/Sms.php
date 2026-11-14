<?php

declare(strict_types=1);

namespace Raits\Voice;

/**
 * One text message. It carries the number it left from, the number it went to,
 * the day, and the count of parts the carrier charged for.
 */
final class Sms
{
    public function __construct(
        public readonly string $id,
        public readonly string $number,
        public readonly string $to,
        public readonly string $day,
        public readonly int $parts = 1,
    ) {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $day) !== 1) {
            throw new \RuntimeException('a message carries its day as YYYY-MM-DD');
        }
        if ($parts < 1) {
            throw new \RuntimeException('a message takes at least one part');
        }
        if (trim($to) === '') {
            throw new \RuntimeException('a message names the number it goes to');
        }
    }

    public function period(): string
    {
        return substr($this->day, 0, 7);
    }
}
