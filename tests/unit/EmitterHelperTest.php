<?php

declare(strict_types=1);

namespace Bviguier\Siglot\Tests\Unit;

use Bviguier\Siglot\Internal\SignalMethod;
use Bviguier\Siglot\Internal\SlotMethod;
use Bviguier\Siglot\Tests\Support\SpyReceiver;
use Bviguier\Siglot\Tests\Support\TestEmitter;
use PHPUnit\Framework\TestCase;

class EmitterHelperTest extends TestCase
{
    public function testEmitterUseCase(): void
    {
        $emitter = new TestEmitter();
        $receiver = new SpyReceiver();

        $emitter->connector(
            SignalMethod::fromClosure($emitter->mySignal(...))
        )->connect(
            SlotMethod::fromClosure($receiver->mySlot(...))
        );

        $emitter->emit($emitter->MySignal());

        self::assertSame(1, $receiver->nbCalls());
    }

}
