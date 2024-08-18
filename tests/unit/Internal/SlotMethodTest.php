<?php

declare(strict_types=1);

namespace Bviguier\Siglot\Tests\Unit\Internal;

use Bviguier\Siglot\Emitter;
use Bviguier\Siglot\Internal\SignalMethod;
use Bviguier\Siglot\Internal\SlotMethod;
use Bviguier\Siglot\SiglotError;
use Bviguier\Siglot\SignalEvent;
use Bviguier\Siglot\Tests\Support\FakeEmitterTrait;
use Bviguier\Siglot\Tests\Support\SpyReceiver;
use PHPUnit\Framework\TestCase;

class SlotMethodTest extends TestCase
{
    public function testSlotMethodCreation(): void
    {
        $receiver = new SpyReceiver();

        $slotMethod = SlotMethod::fromClosure($receiver->mySlot(...));

        self::assertTrue($slotMethod->isValid());
        self::assertSame($receiver, $slotMethod->receiver());
        self::assertSame('mySlot', $slotMethod->name);
    }

    public function testCaseInsensitivity(): void
    {
        $receiver = new SpyReceiver();

        $slotMethod = SlotMethod::fromClosure($receiver->MYSLOT(...));

        self::assertTrue($slotMethod->isValid());
        self::assertSame($receiver, $slotMethod->receiver());
        self::assertSame('mySlot', $slotMethod->name);
    }

    public function testItDoesNotPreventGarbageCollection(): void
    {
        $receiver = new SpyReceiver();

        $slotMethod = SlotMethod::fromClosure($receiver->mySlot(...));

        self::assertTrue($slotMethod->isValid());

        unset($receiver);
        \gc_collect_cycles();

        self::assertFalse($slotMethod->isValid());
    }

    public function testInvocation(): void
    {
        $receiver = new class() {
            /** @return array{0:int,1:string} */
            public function mySlot(int $int, string $string): array
            {
                return [$int, $string];
            }
        };

        $slotMethod = SlotMethod::fromClosure($receiver->mySlot(...));

        self::assertSame([1, 'string'], $slotMethod->invoke([1, 'string']));
    }

    public function testCreationFromPrivateMethod(): void
    {
        $receiver = new class() {
            private function myPrivateSlot(): string
            {
                return 'private';
            }

            public function getSlotMethod(): SlotMethod
            {
                return SlotMethod::fromClosure($this->myPrivateSlot(...));
            }
        };

        $slotMethod = $receiver->getSlotMethod();

        self::assertTrue($slotMethod->isValid());
        self::assertSame($receiver, $slotMethod->receiver());
        self::assertSame('myPrivateSlot', $slotMethod->name);
        self::assertSame('private', $slotMethod->invoke([]));
    }

    public function testExceptionThrownWhenClosureIsNotBoundToObject(): void
    {
        self::expectException(SiglotError::class);
        self::expectExceptionMessage('Closure is not bound to an object');

        SlotMethod::fromClosure(static fn() => null);
    }

    public function testExceptionThrownWhenMethodDoesNotExist(): void
    {
        self::expectException(SiglotError::class);
        self::expectExceptionMessage('Attempt to create a Slot from unknown method');

        SlotMethod::fromClosure(fn() => null);
    }

    public function testFromWrappedSignal(): void
    {
        $object = new class() implements Emitter {
            use FakeEmitterTrait;
            public function mySignal(int $int, string $string): SignalEvent
            {
                return SignalEvent::auto();
            }
        };

        $signalMethod = SignalMethod::fromClosure($object->mySignal(...));
        $slotMethod = SlotMethod::fromWrappedSignal($signalMethod, fn($args) => ['args' => $args]);

        self::assertTrue($slotMethod->isValid());
        self::assertSame($object, $slotMethod->receiver());
        self::assertSame('mySignal', $slotMethod->name);

        $result = $slotMethod->invoke([1, 'string']);

        self::assertSame(['args' => [1, 'string']], $result);

        unset($object);
        \gc_collect_cycles();

        self::assertFalse($slotMethod->isValid());
    }
}
