<?php

declare(strict_types=1);

namespace Raits\Voice;

/**
 * The menu a caller hears. A menu carries a greeting, a list of choices keyed by
 * digit, and the queue a choice lands in. A menu with no choices cannot answer.
 */
final class Ivr
{
    /** @var array<string, string> */
    private array $choices = [];

    public function __construct(
        public readonly string $name,
        public readonly string $greeting,
    ) {
        if (trim($name) === '') {
            throw new \RuntimeException('a menu carries a name');
        }
        if (trim($greeting) === '') {
            throw new \RuntimeException('a menu greets the caller');
        }
    }

    public function choose(string $digit, string $queue): void
    {
        if (preg_match('/^[0-9]$/', $digit) !== 1) {
            throw new \RuntimeException('a choice is one digit');
        }
        if (trim($queue) === '') {
            throw new \RuntimeException('a choice names the queue it lands in');
        }
        $this->choices[$digit] = $queue;
    }

    public function count(): int
    {
        return count($this->choices);
    }

    /** @return array<string, string> */
    public function choices(): array
    {
        $choices = $this->choices;
        ksort($choices);

        return $choices;
    }

    public function queueFor(string $digit): ?string
    {
        return $this->choices[$digit] ?? null;
    }
}
