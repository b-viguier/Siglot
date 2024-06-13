<?php

declare(strict_types=1);

namespace Bviguier\Siglot\Tests\Support;

use Bviguier\Siglot\Emitter;
use Bviguier\Siglot\EmitterHelper;
use Bviguier\Siglot\SignalEvent;

class TestEmitter implements Emitter
{
    use EmitterHelper {
        EmitterHelper::emit as public;
    }

    public function mySignal(): SignalEvent
    {
        return SignalEvent::auto();
    }

    public function mySignal0(): SignalEvent
    {
        return SignalEvent::auto();
    }

    public function mySignal1(int $p1): SignalEvent
    {
        return SignalEvent::auto();
    }

    public function mySignal2(int $p1, int $p2): SignalEvent
    {
        return SignalEvent::auto();
    }

    public function mySignal3(int $p1, int $p2, int $p3): SignalEvent
    {
        return SignalEvent::auto();
    }

    public function mySignal4(int $p1, int $p2, int $p3, int $p4): SignalEvent
    {
        return SignalEvent::auto();
    }

    public function mySignal5(int $p1, int $p2, int $p3, int $p4, int $p5): SignalEvent
    {
        return SignalEvent::auto();
    }
}
