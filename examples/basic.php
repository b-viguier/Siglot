<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Bviguier\Siglot;

$button = new class() implements Siglot\Emitter {
    use Siglot\EmitterHelper;

    public function click(): void
    {
        $this->emit($this->clicked());
    }

    public function clicked(): Siglot\SignalEvent
    {
        return Siglot\SignalEvent::auto();
    }
};

$receiver = new class() {
    public function onClick(): void
    {
        echo "Button clicked!\n";
    }
};

Siglot\Siglot::connect0(
    $button->clicked(...),
    $receiver->onClick(...),
);

$button->click();
// Displays: Button clicked!
