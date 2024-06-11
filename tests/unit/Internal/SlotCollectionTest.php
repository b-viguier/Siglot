<?php

declare(strict_types=1);

namespace Bviguier\Siglot\Tests\Unit\Internal;

use Bviguier\Siglot\Internal\SlotCollection;
use Bviguier\Siglot\Internal\SlotMethod;
use Bviguier\Siglot\SiglotError;
use PHPUnit\Framework\TestCase;

class SlotCollectionTest extends TestCase
{
    public function testAddedSlotsAreInvoked(): void
    {
        $object1 = new class () {
            /** @var array<mixed[]> */
            public array $calls = [];
            public function mySlot(int $int, string $string): void
            {
                $this->calls[] = \func_get_args();
            }
        };
        $object2 = new $object1();

        $slot1 = SlotMethod::fromClosure($object1->mySlot(...));
        $slot2 = SlotMethod::fromClosure($object2->mySlot(...));
        $collection = new SlotCollection();
        $args = [1, 'string'];

        $collection->invoke($args);
        self::assertCount(0, $object1->calls);
        self::assertCount(0, $object2->calls);

        $collection->add($slot1);
        $collection->add($slot2);
        $collection->invoke($args);

        self::assertSame([$args], $object1->calls);
        self::assertSame([$args], $object2->calls);
    }

    public function testRemovedSlotsAreNotInvokedAnymore(): void
    {
        $object = new class () {
            /** @var array<mixed[]> */
            public array $calls = [];
            public function mySlot(int $int, string $string): void
            {
                $this->calls[] = \func_get_args();
            }
        };

        $slot = SlotMethod::fromClosure($object->mySlot(...));
        $collection = new SlotCollection();
        $args = [1, 'string'];

        $collection->add($slot);
        $collection->invoke($args);
        self::assertCount(1, $object->calls);

        $collection->remove($slot);
        $collection->invoke($args);
        self::assertCount(1, $object->calls);
    }

    public function testSlotCanBeRemovedFromAnOtherMethodInstance(): void
    {
        $object = new class () {
            public int $nbCalls = 0;
            public function mySlot(): void
            {
                ++$this->nbCalls;
            }
        };

        $slot1 = SlotMethod::fromClosure($object->mySlot(...));
        $slot2 = SlotMethod::fromClosure($object->mySlot(...));
        $collection = new SlotCollection();

        $collection->add($slot1);
        $collection->remove($slot2);
        self::assertSame(0, $object->nbCalls);
    }

    public function testSlotsAddedTwiceAreInvokedOnce(): void
    {
        $object = new class () {
            /** @var array<mixed[]> */
            public array $calls = [];
            public function mySlot(int $int, string $string): void
            {
                $this->calls[] = \func_get_args();
            }
        };

        $slot1 = SlotMethod::fromClosure($object->mySlot(...));
        $slot2 = SlotMethod::fromClosure($object->mySlot(...));
        $collection = new SlotCollection();
        $args = [1, 'string'];

        $collection->add($slot1);
        $collection->add($slot1);
        $collection->add($slot2);
        $collection->invoke($args);
        self::assertCount(1, $object->calls);
    }

    public function testRemovingUnknownSlotThrowsAnException(): void
    {
        $object = new class () {
            public function mySlot(): void {}
        };

        $slot = SlotMethod::fromClosure($object->mySlot(...));
        $collection = new SlotCollection();

        self::expectException(SiglotError::class);
        self::expectExceptionMessage('Slot not found in collection');
        $collection->remove($slot);
    }

    public function testSeveralSlotsFromSameObjectsAreAllowed(): void
    {
        $object = new class () {
            /** @var array<array{0:string,1:string}> */
            public array $calls = [];
            public function mySlot1(string $string): void
            {
                $this->calls[] = [__FUNCTION__, $string];
            }
            public function mySlot2(string $string): void
            {
                $this->calls[] = [__FUNCTION__, $string];
            }
        };

        $slot1 = SlotMethod::fromClosure($object->mySlot1(...));
        $slot2 = SlotMethod::fromClosure($object->mySlot2(...));
        $collection = new SlotCollection();

        $collection->add($slot1);
        $collection->add($slot2);
        $collection->invoke(['input']);

        self::assertSame([['mySlot1', 'input'], ['mySlot2', 'input']], $object->calls);
    }

    public function testSlotsCanBeAddedDuringInvocation(): void
    {
        $object = new class () {
            public int $nbCalls = 0;

            public function mySlot(): void
            {
                ++$this->nbCalls;
            }
        };

        $addObject = new class () {
            public SlotMethod $slotToAdd;
            public int $nbCalls = 0;

            public function mySlot(SlotCollection $collection): void
            {
                ++$this->nbCalls;
                $collection->add($this->slotToAdd);
            }
        };

        $slot = SlotMethod::fromClosure($object->mySlot(...));
        $addSlot = SlotMethod::fromClosure($addObject->mySlot(...));
        $addObject->slotToAdd = $slot;

        $collection = new SlotCollection();
        $collection->add($addSlot);

        $collection->invoke([$collection]);

        self::assertSame(1, $addObject->nbCalls);
        self::assertSame(1, $object->nbCalls);
    }

    public function testSlotsCanBeRemovedDuringInvocation(): void
    {
        $object1 = new class () {
            public int $nbCalls = 0;

            public function mySlot(): void
            {
                ++$this->nbCalls;
            }
        };
        $object2 = new $object1();
        $object3 = new $object1();


        $removePrevious = new class () {
            public SlotMethod $slotToRemove;
            public int $nbCalls = 0;

            public function mySlot(SlotCollection $collection): void
            {
                ++$this->nbCalls;
                $collection->remove($this->slotToRemove);
            }
        };
        $removeNext = new $removePrevious();
        $removeCurrent = new $removePrevious();

        $slot1 = SlotMethod::fromClosure($object1->mySlot(...));
        $slot2 = SlotMethod::fromClosure($object2->mySlot(...));
        $slot3 = SlotMethod::fromClosure($object3->mySlot(...));

        $removeSlot1 = SlotMethod::fromClosure($removePrevious->mySlot(...));
        $removePrevious->slotToRemove = $slot1;
        $removeSlot3 = SlotMethod::fromClosure($removeNext->mySlot(...));
        $removeNext->slotToRemove = $slot3;
        $removeItself = SlotMethod::fromClosure($removeCurrent->mySlot(...));
        $removeCurrent->slotToRemove = $removeItself;

        $collection = new SlotCollection();
        $collection->add($slot1);
        $collection->add($removeSlot1);
        $collection->add($removeItself);
        $collection->add($removeSlot3);
        $collection->add($slot2);
        $collection->add($slot3);

        $collection->invoke([$collection]);

        self::assertSame(1, $object1->nbCalls);
        self::assertSame(1, $removePrevious->nbCalls);
        self::assertSame(1, $removeCurrent->nbCalls);
        self::assertSame(1, $removeNext->nbCalls);
        self::assertSame(1, $object2->nbCalls);
        self::assertSame(0, $object3->nbCalls);
    }
}
