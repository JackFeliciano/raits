<?php

declare(strict_types=1);

namespace Raits\Tests;

use Raits\Client\Voice;
use Raits\Connect\Ledger;
use Raits\Connect\Source;
use Raits\Connect\SwitchSource;
use Raits\Voice\Board;

$one = new class implements Source {
    public function label(): string
    {
        return 'switch:records';
    }

    public function lines(): array
    {
        return [['kind' => 'call', 'id' => 'c-1']];
    }
};

$two = new class implements Source {
    public function label(): string
    {
        return 'file:numbers.json';
    }

    public function lines(): array
    {
        return [['kind' => 'call', 'id' => 'c-2'], ['kind' => 'sms', 'id' => 's-1']];
    }
};

$silent = new class implements Source {
    public function label(): string
    {
        return 'db:switch/2025/records';
    }

    public function lines(): array
    {
        throw new \RuntimeException('the source did not answer');
    }
};

return [
    ['the ledger counts its sources', static function () use ($one, $two): void {
        $ledger = new Ledger();
        $ledger->add($one);
        $ledger->add($two);
        Assert::same(2, $ledger->count(), 'the source count drifted');
    }],
    ['the ledger names its sources', static function () use ($one, $two): void {
        $ledger = new Ledger();
        $ledger->add($one);
        $ledger->add($two);
        Assert::same(['switch:records', 'file:numbers.json'], $ledger->labels(), 'the labels drifted');
    }],
    ['the ledger keys a source by its label', static function () use ($one): void {
        $ledger = new Ledger();
        $ledger->add($one);
        $ledger->add($one);
        Assert::same(1, $ledger->count(), 'the same label was counted twice');
    }],
    ['the ledger joins every line of every source', static function () use ($one, $two): void {
        $ledger = new Ledger();
        $ledger->add($one);
        $ledger->add($two);
        Assert::same(3, count($ledger->records()), 'the joined count drifted');
    }],
    ['a source that throws stops the read', static function () use ($one, $silent): void {
        $ledger = new Ledger();
        $ledger->add($one);
        $ledger->add($silent);
        try {
            $ledger->read();
            Assert::true(false, 'a silent source passed the read');
        } catch (\RuntimeException $e) {
            Assert::contains('did not answer', $e->getMessage());
        }
    }],
    ['a read over every source succeeds when each one answers', static function () use ($one, $two): void {
        $ledger = new Ledger();
        $ledger->add($one);
        $ledger->add($two);
        $ledger->read();
        Assert::true(true, 'the read reached every source');
    }],
    ['the switch hands its records to the connector', static function (): void {
        $voice = new Voice(new Board());
        $voice->hold('+551140028922', 'BR', 'WestMiller');
        $voice->call('c-1', '+551140028922', '+551199887766', '2026-01-06', 184);
        $source = new SwitchSource($voice);
        Assert::same('switch:records', $source->label(), 'the label drifted');
        $lines = $source->lines();
        Assert::same(1, count($lines), 'the line count drifted');
        Assert::same('c-1', $lines[0]['id'], 'the id drifted');
        Assert::same(184, $lines[0]['seconds'], 'the seconds drifted');
    }],
    ['the connector reads the switch through the ledger', static function (): void {
        $voice = new Voice(new Board());
        $voice->hold('+551140028922', 'BR', 'WestMiller');
        $voice->call('c-1', '+551140028922', '+551199887766', '2026-01-06', 60);
        $voice->message('s-1', '+551140028922', '+551199887766', '2026-01-07', 1);
        $ledger = new Ledger();
        $ledger->add(new SwitchSource($voice));
        Assert::same(2, count($ledger->records()), 'the switch lines drifted');
    }],
];
