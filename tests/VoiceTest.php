<?php

declare(strict_types=1);

namespace Raits\Tests;

use Raits\Client\Voice;
use Raits\Voice\Call;
use Raits\Voice\Ivr;
use Raits\Voice\Number;
use Raits\Voice\Sms;
use Raits\Voice\Board;

$voice = new Voice(new Board());
$voice->hold('+551140028922', 'BR', 'WestMiller', Number::VOICE);
$voice->hold('+551140026644', 'BR', 'WestMiller', Number::MESSAGING);
$voice->call('c-1', '+551140028922', '+551199887766', '2026-01-06', 184);
$voice->call('c-2', '+551140028922', '+551199887744', '2026-02-06', 0, Call::MISSED);
$voice->message('s-1', '+551140026644', '+551199887766', '2026-01-08', 2);
$menu = $voice->menu('main', 'Press one for sales.');
$menu->choose('1', 'sales');
$menu->choose('2', 'billing');

return [
    ['a number carries its country and its carrier', static function (): void {
        $number = new Number('+551140028922', 'BR', 'WestMiller');
        Assert::same('BR', $number->country, 'the country drifted');
        Assert::same('WestMiller', $number->carrier, 'the carrier drifted');
        Assert::true($number->speaks(), 'the number stopped taking voice');
        Assert::true($number->writes(), 'the number stopped taking messages');
    }],
    ['a voice number takes no messages', static function (): void {
        $number = new Number('+551140028922', 'BR', 'WestMiller', Number::VOICE);
        Assert::true($number->speaks(), 'the voice number stopped speaking');
        Assert::same(false, $number->writes(), 'a voice number took a message');
    }],
    ['a number of the wrong shape throws', static function (): void {
        try {
            new Number('12345', 'BR', 'WestMiller');
            Assert::true(false, 'a five digit number was accepted');
        } catch (\RuntimeException $e) {
            Assert::contains('eight to fifteen', $e->getMessage());
        }
    }],
    ['a number answers for voice, messaging or both', static function (): void {
        try {
            new Number('+551140028922', 'BR', 'WestMiller', 'fax');
            Assert::true(false, 'a fourth capability was accepted');
        } catch (\RuntimeException $e) {
            Assert::contains('voice', $e->getMessage());
        }
    }],
    ['a call reads its own month and minutes', static function (): void {
        $call = new Call('c-1', '+551140028922', '+551199887766', '2026-01-06', 184);
        Assert::same('2026-01', $call->period(), 'the month drifted');
        Assert::same(3.07, $call->minutes(), 'the minutes drifted');
    }],
    ['a missed call runs no seconds', static function (): void {
        try {
            new Call('c-2', '+551140028922', '+551199887766', '2026-01-06', 12, Call::MISSED);
            Assert::true(false, 'a missed call carried seconds');
        } catch (\RuntimeException $e) {
            Assert::contains('no seconds', $e->getMessage());
        }
    }],
    ['a call ends answered, missed or busy', static function (): void {
        try {
            new Call('c-3', '+551140028922', '+551199887766', '2026-01-06', 0, 'dropped');
            Assert::true(false, 'a fourth result was accepted');
        } catch (\RuntimeException $e) {
            Assert::contains('answered', $e->getMessage());
        }
    }],
    ['a message takes at least one part', static function (): void {
        try {
            new Sms('s-9', '+551140026644', '+551199887766', '2026-01-08', 0);
            Assert::true(false, 'a message took no part');
        } catch (\RuntimeException $e) {
            Assert::contains('one part', $e->getMessage());
        }
    }],
    ['a menu keys its choices by digit', static function (): void {
        $menu = new Ivr('main', 'Press one.');
        $menu->choose('2', 'billing');
        $menu->choose('1', 'sales');
        Assert::same(['1' => 'sales', '2' => 'billing'], $menu->choices(), 'the choices came back unsorted');
        Assert::same(2, $menu->count(), 'the choice count drifted');
    }],
    ['a choice is one digit', static function (): void {
        try {
            (new Ivr('main', 'Press one.'))->choose('12', 'sales');
            Assert::true(false, 'a two digit choice was accepted');
        } catch (\RuntimeException $e) {
            Assert::contains('one digit', $e->getMessage());
        }
    }],
    ['the switch holds its numbers in order', static function () use ($voice): void {
        Assert::same(['+551140026644', '+551140028922'], $voice->numbers(), 'the numbers came back unsorted');
        Assert::same(2, $voice->board()->count(), 'the number count drifted');
    }],
    ['a call to a number the switch does not hold throws', static function () use ($voice): void {
        try {
            $voice->call('c-9', '+551140029999', '+551199887766', '2026-01-06', 30);
            Assert::true(false, 'a call landed on an unheld number');
        } catch (\RuntimeException $e) {
            Assert::contains('does not hold', $e->getMessage());
        }
    }],
    ['the switch counts the seconds of a number', static function () use ($voice): void {
        Assert::same(184, $voice->board()->seconds('+551140028922'), 'the seconds drifted');
        Assert::same(184, $voice->board()->seconds('+551140028922', '2026-01'), 'the month filter drifted');
        Assert::same(0, $voice->board()->seconds('+551140028922', '2026-02'), 'a missed call carried seconds');
    }],
    ['the switch counts the parts of a number', static function () use ($voice): void {
        Assert::same(2, $voice->board()->parts('+551140026644'), 'the parts drifted');
        Assert::same(0, $voice->board()->parts('+551140026644', '2026-02'), 'the month filter drifted');
    }],
    ['the switch lists the months it carried', static function () use ($voice): void {
        Assert::same(['2026-01', '2026-02'], $voice->board()->periods(), 'the months came back wrong');
    }],
    ['the client reports one number', static function () use ($voice): void {
        $report = $voice->report('+551140028922');
        Assert::same(2, $report['calls'], 'the call count drifted');
        Assert::same(184, $report['seconds'], 'the seconds drifted');
        Assert::same(0, $report['messages'], 'a call number reported messages');
    }],
    ['the client counts the calls of each month', static function () use ($voice): void {
        Assert::same(['2026-01' => 1, '2026-02' => 1], $voice->byPeriod(), 'the month counts drifted');
    }],
    ['a caller lands in the queue the digit opens', static function () use ($voice): void {
        Assert::same('sales', $voice->queue('+551140028922', 'main', '1'), 'the queue drifted');
        Assert::same('billing', $voice->queue('+551140028922', 'main', '2'), 'the queue drifted');
        Assert::same(null, $voice->queue('+551140028922', 'main', '7'), 'an unmapped digit opened a queue');
    }],
    ['a messaging number takes no caller', static function () use ($voice): void {
        Assert::same(null, $voice->queue('+551140026644', 'main', '1'), 'a messaging number routed a caller');
    }],
    ['a menu the switch never opened routes nobody', static function () use ($voice): void {
        Assert::same(null, $voice->queue('+551140028922', 'nowhere', '1'), 'a missing menu routed a caller');
    }],
    ['the client joins the calls and the messages', static function () use ($voice): void {
        $records = $voice->records();
        Assert::same(3, count($records), 'the record count drifted');
        Assert::same('call', $records[0]['kind'], 'the first record kind drifted');
        Assert::same('sms', $records[2]['kind'], 'the last record kind drifted');
        Assert::same(2, $records[2]['parts'], 'the parts drifted');
    }],
];
