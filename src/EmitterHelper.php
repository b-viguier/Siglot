<?php

declare(strict_types=1);

namespace Bviguier\Siglot;

trait EmitterHelper
{
    public function connector(Internal\SignalMethod $signal): Internal\Connector
    {
        $this->signalManager ??= new Internal\SignalManager();

        return $this->signalManager->connector($signal);
    }
    private ?Internal\SignalManager $signalManager = null;

    private function emit(SignalEvent $signalEvent): void
    {
        $this->signalManager?->emit($signalEvent);
    }
}
