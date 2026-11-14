<?php

declare(strict_types=1);

namespace Raits\Connect;

/**
 * The connector layer. A company registers the sources it wants read, and the
 * ledger keys them by name so a page cites the address it read instead of
 * guessing at one. RAITS shares this layer with the other systems in the family.
 */
final class Ledger
{
    /** @var array<string, Source> */
    private array $sources = [];

    public function add(Source $source): void
    {
        $this->sources[$source->label()] = $source;
    }

    public function count(): int
    {
        return count($this->sources);
    }

    /** @return list<string> */
    public function labels(): array
    {
        return array_keys($this->sources);
    }

    /** @return list<array<string, mixed>> */
    public function records(): array
    {
        $all = [];
        foreach ($this->sources as $source) {
            foreach ($source->lines() as $row) {
                $all[] = $row;
            }
        }

        return $all;
    }

    /**
     * Every source has to answer before a page is built. One unreadable table
     * stops the run, and the failure names the source that did not answer.
     */
    public function read(): void
    {
        foreach ($this->sources as $source) {
            $source->lines();
        }
    }
}
