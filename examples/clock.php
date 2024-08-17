<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Bviguier\Siglot;

/*======================================================================================================================
Class ClockDisplay
A class to display the current time in a given format.
======================================================================================================================*/
class ClockDisplay
{
    public function __construct(private string $format) {}

    public function display(): void
    {
        $d = new \DateTimeImmutable('now');

        printf(
            "[%s]\t%s\n",
            $this->format,
            $d->format($this->format),
        );
    }
}

/*======================================================================================================================
Class Ticker
A class to emit a signal every second.
======================================================================================================================*/
class Ticker implements Siglot\Emitter
{
    use Siglot\EmitterHelper;

    public function start(int $count): void
    {
        while ($count--) {
            sleep(1);
            $this->emit($this->tick());
        }
    }

    public function tick(): Siglot\SignalEvent
    {
        return Siglot\SignalEvent::auto();
    }
}

/*======================================================================================================================
Main
======================================================================================================================*/
$displayW3C = new ClockDisplay(\DateTimeInterface::W3C);
$display7231 = new ClockDisplay(\DateTimeInterface::RFC7231);
$ticker = new Ticker();
Siglot\Siglot::connect0($ticker->tick(...), $displayW3C->display(...));
Siglot\Siglot::connect0($ticker->tick(...), $display7231->display(...));

$ticker->start(5);
