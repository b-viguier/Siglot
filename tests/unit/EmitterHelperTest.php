<?php

declare(strict_types=1);

namespace Bviguier\Siglot\Tests\Unit;

use Bviguier\Siglot\Emitter;
use Bviguier\Siglot\EmitterHelper;
use Bviguier\Siglot\Internal\SignalMethod;
use Bviguier\Siglot\Internal\SlotMethod;
use Bviguier\Siglot\SignalEvent;
use PHPUnit\Framework\TestCase;

class EmitterHelperTest extends TestCase
{
    public function testEmitterUseCase(): void
    {
        $emitter = new class () implements Emitter {
            use EmitterHelper;

            public function mySignal(): SignalEvent
            {
                return SignalEvent::auto();
            }

            public function emitMySignal(): void
            {
                $this->emit($this->mySignal());
            }
        };

        $receiver = new class () {
            public int $nbCalls = 0;
            public function mySlot(): void
            {
                ++$this->nbCalls;
            }
        };

        $emitter->connector(
            SignalMethod::fromClosure($emitter->mySignal(...))
        )->connect(
            SlotMethod::fromClosure($receiver->mySlot(...))
        );

        $emitter->emitMySignal();

        self::assertSame(1, $receiver->nbCalls);
    }

}
