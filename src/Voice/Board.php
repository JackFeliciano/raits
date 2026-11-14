<?php

declare(strict_types=1);

namespace Raits\Voice;

/**
 * The company's own switch board. It holds the numbers, the calls, the messages
 * and the menus, and it answers the questions a client asks: what did this
 * number take this month, how long did the calls run, and where does a caller
 * land.
 */
final class Board
{
    /** @var array<string, Number> */
    private array $numbers = [];
    /** @var list<Call> */
    private array $calls = [];
    /** @var list<Sms> */
    private array $messages = [];
    /** @var array<string, Ivr> */
    private array $menus = [];

    public function hold(Number $number): void
    {
        $this->numbers[$number->digits] = $number;
    }

    public function count(): int
    {
        return count($this->numbers);
    }

    /** @return list<string> */
    public function digits(): array
    {
        $all = array_keys($this->numbers);
        sort($all);

        return $all;
    }

    public function number(string $digits): ?Number
    {
        return $this->numbers[$digits] ?? null;
    }

    public function record(Call $call): void
    {
        if (!isset($this->numbers[$call->number])) {
            throw new \RuntimeException('a call belongs to a number the board does not hold');
        }
        $this->calls[] = $call;
    }

    public function recordSms(Sms $sms): void
    {
        if (!isset($this->numbers[$sms->number])) {
            throw new \RuntimeException('a message leaves from a number the board does not hold');
        }
        $this->messages[] = $sms;
    }

    /** @return list<Call> */
    public function calls(): array
    {
        return $this->calls;
    }

    /** @return list<Sms> */
    public function messages(): array
    {
        return $this->messages;
    }

    /** @return list<Call> */
    public function callsOn(string $number, string $period = ''): array
    {
        $out = [];
        foreach ($this->calls as $call) {
            if ($call->number !== $number) {
                continue;
            }
            if ($period !== '' && $call->period() !== $period) {
                continue;
            }
            $out[] = $call;
        }

        return $out;
    }

    /** @return list<Sms> */
    public function messagesOn(string $number, string $period = ''): array
    {
        $out = [];
        foreach ($this->messages as $sms) {
            if ($sms->number !== $number) {
                continue;
            }
            if ($period !== '' && $sms->period() !== $period) {
                continue;
            }
            $out[] = $sms;
        }

        return $out;
    }

    public function seconds(string $number, string $period = ''): int
    {
        $total = 0;
        foreach ($this->callsOn($number, $period) as $call) {
            $total += $call->seconds;
        }

        return $total;
    }

    public function parts(string $number, string $period = ''): int
    {
        $total = 0;
        foreach ($this->messagesOn($number, $period) as $sms) {
            $total += $sms->parts;
        }

        return $total;
    }

    /** @return list<string> */
    public function periods(): array
    {
        $seen = [];
        foreach ($this->calls as $call) {
            $seen[$call->period()] = true;
        }
        foreach ($this->messages as $sms) {
            $seen[$sms->period()] = true;
        }
        $periods = array_keys($seen);
        sort($periods);

        return $periods;
    }

    public function open(Ivr $menu): void
    {
        $this->menus[$menu->name] = $menu;
    }

    /** @return list<string> */
    public function menuNames(): array
    {
        $names = array_keys($this->menus);
        sort($names);

        return $names;
    }

    public function menu(string $name): ?Ivr
    {
        return $this->menus[$name] ?? null;
    }

    /**
     * The caller presses a digit on a number and the board answers with the
     * queue that digit opens, or with nothing when the menu has no such choice.
     */
    public function route(string $number, string $menuName, string $digit): ?string
    {
        $held = $this->number($number);
        if ($held === null || !$held->speaks()) {
            return null;
        }
        $menu = $this->menu($menuName);
        if ($menu === null) {
            return null;
        }

        return $menu->queueFor($digit);
    }
}
