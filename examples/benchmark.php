<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Bviguier\Siglot;

const NB_ITERATIONS = 1_000_000;

$receiver = new class() {
    public function slot(): void {}
    public function slot1(): void {}
    public function slot2(): void {}
    public function slot3(): void {}
    public function slot4(): void {}
    public function slot5(): void {}
    public function slot6(): void {}
    public function slot7(): void {}
    public function slot8(): void {}
    public function slot9(): void {}
    public function slot10(): void {}
};

$emitter = new class() implements Siglot\Emitter {
    use Siglot\EmitterHelper;
    public function signal(): Siglot\SignalEvent
    {
        return Siglot\SignalEvent::auto();
    }

    public function signal1(): Siglot\SignalEvent
    {
        return Siglot\SignalEvent::auto();
    }

    public function signal2(): Siglot\SignalEvent
    {
        return Siglot\SignalEvent::auto();
    }

    public function signal3(): Siglot\SignalEvent
    {
        return Siglot\SignalEvent::auto();
    }

    public function signal4(): Siglot\SignalEvent
    {
        return Siglot\SignalEvent::auto();
    }

    public function signal5(): Siglot\SignalEvent
    {
        return Siglot\SignalEvent::auto();
    }

    public function signal6(): Siglot\SignalEvent
    {
        return Siglot\SignalEvent::auto();
    }

    public function signal7(): Siglot\SignalEvent
    {
        return Siglot\SignalEvent::auto();
    }

    public function signal8(): Siglot\SignalEvent
    {
        return Siglot\SignalEvent::auto();
    }

    public function signal9(): Siglot\SignalEvent
    {
        return Siglot\SignalEvent::auto();
    }

    public function signal10(): Siglot\SignalEvent
    {
        return Siglot\SignalEvent::auto();
    }

    public function benchmark(): float
    {
        $startTime = microtime(true);
        for ($i = 0; $i < NB_ITERATIONS; $i++) {
            $this->emit($this->signal());
        }
        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        echo 'Siglot Time: ', $duration, 's', \PHP_EOL;

        return $duration;
    }
};

Siglot\Siglot::connect0(
    $emitter->signal(...),
    $receiver->slot(...),
);

// Creating a lot of (useless) connections
for($slotId = 1; $slotId <= 10; ++$slotId) {
    for($signalId = 1; $signalId <= 10; ++$signalId) {
        Siglot\Siglot::connect0(
            $emitter->{"signal$signalId"}(...),
            $receiver->{"slot$slotId"}(...),
        );
    }
}


echo "Benchmarking with ", NB_ITERATIONS, " iterations...", \PHP_EOL;
$siglotDuration = $emitter->benchmark();

$startTime = microtime(true);
for ($i = 0; $i < NB_ITERATIONS; $i++) {
    $receiver->slot();
}
$endTime = microtime(true);
$nativeDuration = $endTime - $startTime;
echo 'Native Time: ', $nativeDuration, 's', \PHP_EOL;

$overhead = ($siglotDuration - $nativeDuration) / NB_ITERATIONS;
echo 'Overhead: ', $overhead, 's per call', \PHP_EOL;
