<?php

declare(strict_types=1);

namespace Raits\Home;

use Raits\Client\Voice;
use Raits\Voice\Call;
use Raits\Voice\Number;
use Raits\Voice\Board;

/**
 * A company that runs three numbers over four months: a main line, a sales
 * line and a messaging number. The numbers, callers and figures are invented
 * and they belong to this demo only.
 */
final class Sample
{
    private Voice $voice;

    public function __construct()
    {
        $this->voice = new Voice(new Board());
        $this->voice->hold('+551140028922', 'BR', 'WestMiller', Number::VOICE);
        $this->voice->hold('+551140027733', 'BR', 'WestMiller', Number::BOTH);
        $this->voice->hold('+551140026644', 'BR', 'WestMiller', Number::MESSAGING);

        $main = $this->voice->menu('main', 'WestMiller. Press one for sales, two for billing.');
        $main->choose('1', 'sales');
        $main->choose('2', 'billing');
        $main->choose('0', 'front desk');

        $after = $this->voice->menu('after hours', 'WestMiller is closed. Press one to leave a message.');
        $after->choose('1', 'voicemail');

        $this->voice->call('c-1', '+551140028922', '+551199887766', '2026-01-06', 184);
        $this->voice->call('c-2', '+551140028922', '+551199887744', '2026-01-06', 42);
        $this->voice->call('c-3', '+551140027733', '+551198877665', '2026-01-19', 0, Call::MISSED);
        $this->voice->call('c-4', '+551140028922', '+551191122334', '2026-02-03', 311);
        $this->voice->call('c-5', '+551140027733', '+551193344556', '2026-02-11', 0, Call::BUSY);
        $this->voice->call('c-6', '+551140027733', '+551195566778', '2026-02-24', 97);
        $this->voice->call('c-7', '+551140028922', '+551197788990', '2026-03-05', 233);
        $this->voice->call('c-8', '+551140027733', '+551199001122', '2026-03-17', 158);
        $this->voice->call('c-9', '+551140028922', '+551192233445', '2026-04-02', 64);
        $this->voice->call('c-10', '+551140027733', '+551193544667', '2026-04-21', 129);

        $this->voice->message('s-1', '+551140026644', '+551199887766', '2026-01-08', 1);
        $this->voice->message('s-2', '+551140026644', '+551191122334', '2026-01-21', 2);
        $this->voice->message('s-3', '+551140027733', '+551198877665', '2026-02-06', 1);
        $this->voice->message('s-4', '+551140026644', '+551193344556', '2026-02-18', 3);
        $this->voice->message('s-5', '+551140026644', '+551195566778', '2026-03-09', 1);
        $this->voice->message('s-6', '+551140027733', '+551197788990', '2026-04-14', 2);
    }

    public function board(): Board
    {
        return $this->voice->board();
    }

    public function voice(): Voice
    {
        return $this->voice;
    }

    /** @return list<array<string, mixed>> */
    public function perNumber(): array
    {
        $out = [];
        foreach ($this->voice->numbers() as $digits) {
            $out[] = $this->voice->report($digits);
        }

        return $out;
    }

    /** @return array<string, int> */
    public function countsByResult(): array
    {
        $out = [Call::ANSWERED => 0, Call::MISSED => 0, Call::BUSY => 0];
        foreach ($this->voice->board()->calls() as $call) {
            $out[$call->result]++;
        }

        return $out;
    }
}
