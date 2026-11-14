<?php

declare(strict_types=1);

namespace Raits\Client;

use Raits\Voice\Call;
use Raits\Voice\Ivr;
use Raits\Voice\Number;
use Raits\Voice\Sms;
use Raits\Voice\Board;

/**
 * The one entry point a company's own code uses. It holds a board of records, and every
 * call on the telephone side goes through a method named here, so a company
 * never reaches into the records behind the library.
 */
final class Voice
{
    public function __construct(private readonly Board $board)
    {
    }

    public function board(): Board
    {
        return $this->board;
    }

    public function hold(string $digits, string $country, string $carrier, string $capability = Number::BOTH): Number
    {
        $number = new Number($digits, $country, $carrier, $capability);
        $this->board->hold($number);

        return $number;
    }

    /** @return list<string> */
    public function numbers(): array
    {
        return $this->board->digits();
    }

    public function call(
        string $id,
        string $number,
        string $from,
        string $day,
        int $seconds,
        string $result = Call::ANSWERED
    ): Call {
        $call = new Call($id, $number, $from, $day, $seconds, $result);
        $this->board->record($call);

        return $call;
    }

    public function message(string $id, string $number, string $to, string $day, int $parts = 1): Sms
    {
        $sms = new Sms($id, $number, $to, $day, $parts);
        $this->board->recordSms($sms);

        return $sms;
    }

    public function menu(string $name, string $greeting): Ivr
    {
        $menu = new Ivr($name, $greeting);
        $this->board->open($menu);

        return $menu;
    }

    /** @return list<string> */
    public function menus(): array
    {
        return $this->board->menuNames();
    }

    public function queue(string $number, string $menu, string $digit): ?string
    {
        return $this->board->route($number, $menu, $digit);
    }

    /** @return array<string, mixed> */
    public function report(string $number, string $period = ''): array
    {
        return [
            'number' => $number,
            'period' => $period,
            'calls' => count($this->board->callsOn($number, $period)),
            'seconds' => $this->board->seconds($number, $period),
            'messages' => count($this->board->messagesOn($number, $period)),
            'parts' => $this->board->parts($number, $period),
        ];
    }

    /** @return array<string, int> */
    public function byPeriod(): array
    {
        $out = [];
        foreach ($this->board->periods() as $period) {
            $out[$period] = 0;
            foreach ($this->board->calls() as $call) {
                if ($call->period() === $period) {
                    $out[$period]++;
                }
            }
        }

        return $out;
    }

    /** @return list<array<string, mixed>> */
    public function records(): array
    {
        $out = [];
        foreach ($this->board->calls() as $call) {
            $out[] = [
                'kind' => 'call',
                'id' => $call->id,
                'number' => $call->number,
                'day' => $call->day,
                'seconds' => $call->seconds,
                'result' => $call->result,
            ];
        }
        foreach ($this->board->messages() as $sms) {
            $out[] = [
                'kind' => 'sms',
                'id' => $sms->id,
                'number' => $sms->number,
                'day' => $sms->day,
                'parts' => $sms->parts,
                'result' => 'sent',
            ];
        }

        return $out;
    }
}
