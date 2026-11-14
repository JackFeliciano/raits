<?php

declare(strict_types=1);

namespace Raits\Connect;

use Raits\Client\Voice;

/**
 * The client library read through the connector. A company that runs its
 * telephone on RAITS hands the records to any other reader of the family
 * through the same layer the other systems use.
 */
final class SwitchSource implements Source
{
    public function __construct(private readonly Voice $voice)
    {
    }

    public function label(): string
    {
        return 'switch:records';
    }

    /** @return list<array<string, mixed>> */
    public function lines(): array
    {
        return $this->voice->records();
    }
}
