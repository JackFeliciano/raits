<?php

declare(strict_types=1);

namespace Raits\App;

use Raits\Home\Sample;

/**
 * Every page RAITS prints. The markup is written by hand: no template engine,
 * no escaping library beyond htmlspecialchars, so a reader can follow a call
 * from the switch to the table.
 */
final class Views
{
    public function __construct(private readonly string $base)
    {
    }

    private function url(string $path): string
    {
        $base = '/' . trim($this->base, '/');

        return ($base === '/' ? '' : $base) . $path;
    }

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    private function table(array $head, array $rows, string $caption = ''): string
    {
        $html = '<table>';
        if ($caption !== '') {
            $html .= '<caption class="stage">' . $this->e($caption) . '</caption>';
        }
        $html .= '<thead><tr>';
        foreach ($head as $cell) {
            $html .= '<th>' . $this->e($cell) . '</th>';
        }
        $html .= '</tr></thead><tbody>';
        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td>' . $this->e((string) $cell) . '</td>';
            }
            $html .= '</tr>';
        }

        return $html . '</tbody></table>';
    }

    private function head(string $title): string
    {
        return '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>' . $this->e($title) . '</title>
<link rel="stylesheet" href="' . $this->e($this->url('/assets/site.css')) . '">
</head>
<body>
<header>
<a class="brand" href="' . $this->e($this->url('/')) . '">RAITS</a>
<nav>
<a href="' . $this->e($this->url('/')) . '">Overview</a>
<a href="' . $this->e($this->url('/status')) . '">Status</a>
</nav>
</header>
<main>
';
    }

    private function foot(): string
    {
        return "</main>
</body>
</html>
";
    }

    public function front(Sample $sample): string
    {
        $voice = $sample->voice();
        $board = $voice->board();

        $html = $this->head('RAITS');
        $html .= '<h1>RAITS</h1>';
        $html .= '<p class="lede">A company holds its numbers, its call records, its text messages and its menus '
            . 'in one switch, and reaches every one of them through a single client library.</p>';

        $html .= '<h2>The numbers</h2>';
        $rows = [];
        foreach ($voice->numbers() as $digits) {
            $number = $board->number($digits);
            $rows[] = [
                $digits,
                $number->country,
                $number->carrier,
                $number->capability,
                $number->speaks() ? 'yes' : 'no',
                $number->writes() ? 'yes' : 'no',
            ];
        }
        $html .= $this->table(['Number', 'Country', 'Carrier', 'Answers for', 'Voice', 'Messages'], $rows, 'What the company holds');

        $html .= '<h2>Traffic by number</h2>';
        $rows = [];
        foreach ($sample->perNumber() as $report) {
            $rows[] = [
                $report['number'],
                $report['calls'],
                $report['seconds'],
                $report['messages'],
                $report['parts'],
            ];
        }
        $html .= $this->table(['Number', 'Calls', 'Seconds', 'Messages', 'Parts'], $rows, 'Everything the switch carried');

        $html .= '<h2>Calls by result</h2>';
        $rows = [];
        foreach ($sample->countsByResult() as $result => $count) {
            $rows[] = [$result, $count];
        }
        $html .= $this->table(['Result', 'Calls'], $rows, 'How the calls ended');

        $html .= '<h2>Calls by month</h2>';
        $rows = [];
        foreach ($voice->byPeriod() as $period => $count) {
            $rows[] = [$period, $count];
        }
        $html .= $this->table(['Month', 'Calls'], $rows, 'What came in each month');

        $html .= '<h2>The call records</h2>';
        $rows = [];
        foreach ($board->calls() as $call) {
            $rows[] = [$call->id, $call->number, $call->from, $call->day, $call->seconds, $call->result];
        }
        $html .= $this->table(['Id', 'Number', 'Caller', 'Day', 'Seconds', 'Result'], $rows, 'One line per call');

        $html .= '<h2>The messages</h2>';
        $rows = [];
        foreach ($board->messages() as $sms) {
            $rows[] = [$sms->id, $sms->number, $sms->to, $sms->day, $sms->parts];
        }
        $html .= $this->table(['Id', 'Number', 'To', 'Day', 'Parts'], $rows, 'One line per text message');

        $html .= '<h2>The menus</h2>';
        $rows = [];
        foreach ($voice->menus() as $name) {
            $menu = $board->menu($name);
            foreach ($menu->choices() as $digit => $queue) {
                $rows[] = [$name, $menu->greeting, $digit, $queue];
            }
        }
        $html .= $this->table(['Menu', 'Greeting', 'Digit', 'Queue'], $rows, 'Where a caller lands');

        return $html . $this->foot();
    }

    public function status(Sample $sample): string
    {
        $voice = $sample->voice();

        $html = $this->head('RAITS status');
        $html .= '<h1>Status</h1>';
        $html .= $this->table(['Measure', 'Value'], [
            ['numbers', count($voice->numbers())],
            ['calls', count($voice->board()->calls())],
            ['messages', count($voice->board()->messages())],
            ['menus', count($voice->menus())],
            ['months', count($voice->byPeriod())],
        ], 'What the switch holds');

        return $html . $this->foot();
    }

    public function notFound(string $path): string
    {
        $html = $this->head('Not found');
        $html .= '<h1>Not found</h1>';
        $html .= '<p class="figure">Nothing answers at ' . $this->e($path) . '.</p>';

        return $html . $this->foot();
    }
}
