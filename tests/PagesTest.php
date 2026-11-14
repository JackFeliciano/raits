<?php

declare(strict_types=1);

namespace Raits\Tests;

use Raits\App\Front;
use Raits\App\Router;
use Raits\App\Views;

$router = new Router('/raits/');
$front = new Front(new Views('/raits/'));
$page = $front->handle('/raits/', $router);
$status = $front->handle('/raits/status', $router);
$missing = $front->handle('/raits/nowhere', $router);

return [
    ['the router names the front page', static function () use ($router): void {
        Assert::same('front', $router->route('/raits/'), 'the front route drifted');
    }],
    ['the router names the status page', static function () use ($router): void {
        Assert::same('status', $router->route('/raits/status'), 'the status route drifted');
    }],
    ['the router names an unknown path', static function () use ($router): void {
        Assert::same('missing', $router->route('/raits/nowhere'), 'the missing route drifted');
    }],
    ['the router strips its base once', static function () use ($router): void {
        Assert::same('/', $router->strip('/raits/'), 'the stripped path drifted');
        Assert::same('/status', $router->strip('/raits/status'), 'the stripped path drifted');
    }],
    ['the front page carries the product name', static function () use ($page): void {
        Assert::contains('<h1>RAITS</h1>', $page);
    }],
    ['the front page prints the numbers', static function () use ($page): void {
        Assert::contains('What the company holds', $page);
        Assert::contains('+551140028922', $page);
    }],
    ['the front page prints the traffic by number', static function () use ($page): void {
        Assert::contains('Everything the switch carried', $page);
    }],
    ['the front page prints the results', static function () use ($page): void {
        Assert::contains('How the calls ended', $page);
        Assert::contains('missed', $page);
    }],
    ['the front page prints the call records', static function () use ($page): void {
        Assert::contains('One line per call', $page);
        Assert::contains('c-1', $page);
    }],
    ['the front page prints the messages', static function () use ($page): void {
        Assert::contains('One line per text message', $page);
        Assert::contains('s-1', $page);
    }],
    ['the front page prints the menus', static function () use ($page): void {
        Assert::contains('Where a caller lands', $page);
        Assert::contains('sales', $page);
    }],
    ['the status page reports the switch', static function () use ($status): void {
        Assert::contains('What the switch holds', $status);
        Assert::contains('numbers', $status);
    }],
    ['an unknown path answers as not found', static function () use ($missing): void {
        Assert::contains('Nothing answers at', $missing);
    }],
];
