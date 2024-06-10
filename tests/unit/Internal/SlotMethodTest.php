<?php

declare(strict_types=1);

namespace Bviguier\Siglot\Tests\Unit\Internal;

use Bviguier\Siglot\Internal\SlotMethod;
use Bviguier\Siglot\SiglotError;
use PHPUnit\Framework\TestCase;

class SlotMethodTest extends TestCase
{
    public function testSlotMethodCreation(): void
    {
        $object = new class () {
            public function mySlot(): void {}
        };

        $slotMethod = SlotMethod::fromClosure($object->mySlot(...));

        self::assertTrue($slotMethod->isValid());
        self::assertSame($object, $slotMethod->object());
        self::assertSame('mySlot', $slotMethod->name);
    }

    public function testItDoesNotPreventGarbageCollection(): void
    {
        $object = new class () {
            public function mySlot(): void {}
        };

        $slotMethod = SlotMethod::fromClosure($object->mySlot(...));

        self::assertTrue($slotMethod->isValid());

        unset($object);
        \gc_collect_cycles();

        self::assertFalse($slotMethod->isValid());
    }

    public function testInvocation(): void
    {
        $object = new class () {
            /** @return array{0:int,1:string} */
            public function mySlot(int $int, string $string): array
            {
                return [$int, $string];
            }
        };

        $slotMethod = SlotMethod::fromClosure($object->mySlot(...));

        self::assertSame([1, 'string'], $slotMethod->invoke([1, 'string']));
    }

    public function testCreationFromPrivateMethod(): void
    {
        $object = new class () {
            private function myPrivateSlot(): string
            {
                return 'private';
            }

            public function getSlotMethod(): SlotMethod
            {
                return SlotMethod::fromClosure($this->myPrivateSlot(...));
            }
        };

        $slotMethod = $object->getSlotMethod();

        self::assertTrue($slotMethod->isValid());
        self::assertSame($object, $slotMethod->object());
        self::assertSame('myPrivateSlot', $slotMethod->name);
        self::assertSame('private', $slotMethod->invoke([]));
    }

    public function testExceptionThrownWhenClosureIsNotBoundToObject(): void
    {
        $this->expectException(SiglotError::class);
        $this->expectExceptionMessage('Closure is not bound to an object');

        SlotMethod::fromClosure(static fn() => null);
    }

    public function testExceptionThrownWhenMethodDoesNotExist(): void
    {
        $this->expectException(SiglotError::class);
        $this->expectExceptionMessage('Attempt to create a Slot from unknown method');

        SlotMethod::fromClosure(fn() => null);
    }
}
