<?php

declare(strict_types=1);

namespace Raits\Connect;

/**
 * One book a company points RAITS at. A source opens, hands back lines, and
 * says what it is so the page can print the address it read.
 */
interface Source
{
    public function label(): string;

    /** @return list<array<string, mixed>> */
    public function lines(): array;
}
