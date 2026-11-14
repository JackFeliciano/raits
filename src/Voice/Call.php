<?php

declare(strict_types=1);

namespace Raits\Voice;

/**
 * One call record. It names the number that answered, the caller, the day it
 * started, how long it ran in whole seconds, and how it ended.
 */
final class Call
{
    public const ANSWERED = 'answered';
    public const MISSED = 'missed';
    public const BUSY = 'busy';

    public function __construct(
        public readonly string $id,
        public readonly string $number,
        public readonly string $from,
        public readonly string $day,
        public readonly int $seconds,
        public readonly string $result = self::ANSWERED,
    ) {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $day) !== 1) {
            throw new \RuntimeException('a call carries its day as YYYY-MM-DD');
        }
        if ($seconds < 0) {
            throw new \RuntimeException('a call runs for zero seconds or more');
        }
        if (!in_array($result, [self::ANSWERED, self::MISSED, self::BUSY], true)) {
            throw new \RuntimeException('a call ends answered, missed, or busy');
        }
        if ($result !== self::ANSWERED && $seconds > 0) {
            throw new \RuntimeException('a call that never connected runs no seconds');
        }
    }

    public function period(): string
    {
        return substr($this->day, 0, 7);
    }

    public function minutes(): float
    {
        return round($this->seconds / 60, 2);
    }
}
