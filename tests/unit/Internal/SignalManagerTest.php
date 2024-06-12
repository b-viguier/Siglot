<?php

declare(strict_types=1);

namespace Bviguier\Siglot\Tests\Unit\Internal;

use Bviguier\Siglot\Internal\SignalManager;
use Bviguier\Siglot\Internal\SignalMethod;
use Bviguier\Siglot\Internal\SlotMethod;
use Bviguier\Siglot\Tests\Support\SpyReceiver;
use Bviguier\Siglot\Tests\Support\TestEmitter;
use PHPUnit\Framework\TestCase;

class SignalManagerTest extends TestCase
{
    public function testEmittedSignalsAreForwardedToSlotConnectedThroughConnector(): void
    {
        $signalManager = new SignalManager();
        $signal = SignalMethod::fromClosure(
            ($emitter = new TestEmitter())->mySignal(...)
        );
        $slot = SlotMethod::fromClosure(
            ($receiver = new SpyReceiver())->mySlot(...),
        );

        $signalManager
            ->connector($signal)
            ->connect($slot);

        $signalManager->emit($emitter->mySignal());

        self::assertSame(1, $receiver->nbCalls());
    }
}
