<?php

declare(strict_types=1);

namespace Bviguier\Siglot\Tests\Unit\Internal;

use Bviguier\Siglot\Emitter;
use Bviguier\Siglot\Internal\SignalManager;
use Bviguier\Siglot\Internal\SignalMethod;
use Bviguier\Siglot\Internal\SlotMethod;
use Bviguier\Siglot\SignalEvent;
use Bviguier\Siglot\Tests\Support\FakeEmitterTrait;
use PHPUnit\Framework\TestCase;

class SignalManagerTest extends TestCase
{
    public function testEmittedSignalsAreForwardedToSlotConnectedThroughConnector(): void
    {
        $signalManager = new SignalManager();
        $signal = SignalMethod::fromClosure(
            ($signalObject = new class () implements Emitter {
                use FakeEmitterTrait;
                public function mySignal(): SignalEvent
                {
                    return SignalEvent::auto();
                }
            })->mySignal(...)
        );
        $slot = SlotMethod::fromClosure(
            ($slotObject = new class () {
                public int $nbCalls = 0;
                public function mySlot(): void
                {
                    ++$this->nbCalls;
                }
            })->mySlot(...),
        );

        $signalManager
            ->connector($signal)
            ->connect($slot);

        $signalManager->emit($signalObject->mySignal());

        self::assertSame(1, $slotObject->nbCalls);
    }
}
