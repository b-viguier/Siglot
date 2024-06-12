<?php

declare(strict_types=1);

namespace Bviguier\Siglot\Tests\Support;

use Bviguier\Siglot\Emitter;
use Bviguier\Siglot\EmitterHelper;
use Bviguier\Siglot\SignalEvent;

class TestEmitter implements Emitter
{
    use EmitterHelper;

    public function mySignal(): SignalEvent
    {
        return SignalEvent::auto();
    }

    public function emitMySignal(): void
    {
        $this->emit($this->mySignal());
    }
}
