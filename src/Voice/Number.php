<?php

declare(strict_types=1);

namespace Raits\Voice;

/**
 * One number the company holds. A number carries an area code, a country, the
 * company that issued it, and the capability it answers for: voice, messaging,
 * or both.
 */
final class Number
{
    public const VOICE = 'voice';
    public const MESSAGING = 'messaging';
    public const BOTH = 'both';

    public function __construct(
        public readonly string $digits,
        public readonly string $country,
        public readonly string $carrier,
        public readonly string $capability = self::BOTH,
    ) {
        if (preg_match('/^\+?[1-9]\d{7,14}$/', $digits) !== 1) {
            throw new \RuntimeException('a number is eight to fifteen digits and may lead with a plus');
        }
        if (!in_array($capability, [self::VOICE, self::MESSAGING, self::BOTH], true)) {
            throw new \RuntimeException('a number answers for voice, for messaging, or for both');
        }
        if (trim($country) === '') {
            throw new \RuntimeException('a number carries its country');
        }
    }

    public function speaks(): bool
    {
        return $this->capability === self::VOICE || $this->capability === self::BOTH;
    }

    public function writes(): bool
    {
        return $this->capability === self::MESSAGING || $this->capability === self::BOTH;
    }
}
