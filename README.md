# RAITS

## What RAITS is

RAITS gives a company its own telephony. The company holds direct inward dialling numbers. RAITS records every call. It sends text messages and records each one with the count of parts the carrier charged. It builds the menus a caller hears.

Every part of the system sits behind a single client library. The company's own code calls one place. The library integrates to APIX, the interface protocol of the ecosystem.

RAITS never places a call and never delivers a message itself. It holds the record of what the carrier did. It publishes no data of its own. Every record that leaves the system goes out through the connector layer.

## How a number is held

RAITS holds each direct inward dialling number as one record. The record carries the number, a country, and a carrier. It also states what the number answers: voice, messaging, or both.

A number that answers voice takes calls. A number that answers messaging takes text messages. A number that answers both takes both.

## How a call is recorded

RAITS records every call. The record holds the number that answered, the caller, the day, and the seconds the call ran. It also holds how the call ended. A call ends as answered, missed, or busy.

The carrier handles the call. RAITS never places a call. RAITS keeps the record of what the carrier did.

## How a menu routes a caller

RAITS builds a menu from a greeting and a set of choices. Each choice has one digit as its key. Each choice lands in a queue.

Routing takes three inputs: a number, a menu, and a digit. The company's code hands those to RAITS. RAITS gives back the queue.

## The report

RAITS reports traffic per number and per month. For each number in each month, the report gives the calls, the seconds, the messages, and the parts. The call records supply the calls and the seconds. The message records supply the messages and the parts.

## The client library

The client library is the one entry point to RAITS. Numbers, calls, messages, menus, routing, and reports all sit behind it. The company's own code calls the library and nothing else.

The library integrates to APIX, the interface protocol of the ecosystem.

## The connector

The connector layer hands every record to the rest of the family. Numbers, calls, messages, menus, and report lines all go out through it. This is the one way data leaves RAITS.

## Running it

RAITS needs PHP 8.2 or later. It needs no Composer and no vendor directory.

Set `RAITS_BASE` to `/raits/`.

Start the showroom with:

```
php -S 127.0.0.1:8123 -t showroom showroom/index.php
```

The command starts the built-in PHP server on 127.0.0.1:8123 and serves the showroom. The showroom holds a demo company with three numbers and four months of traffic.

## The tests

Run the tests with:

```
php tests/run.php
```

The tests need nothing beyond PHP.
