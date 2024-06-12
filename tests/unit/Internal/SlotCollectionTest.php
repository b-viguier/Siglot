<?php

declare(strict_types=1);

namespace Bviguier\Siglot\Tests\Unit\Internal;

use Bviguier\Siglot\Internal\SlotCollection;
use Bviguier\Siglot\Internal\SlotMethod;
use Bviguier\Siglot\SiglotError;
use Bviguier\Siglot\Tests\Support\SpyReceiver;
use PHPUnit\Framework\TestCase;

class SlotCollectionTest extends TestCase
{
    public function testAddedSlotsAreInvoked(): void
    {
        $receiver1 = new SpyReceiver();
        $receiver2 = new SpyReceiver();

        $slot1 = SlotMethod::fromClosure($receiver1->mySlot(...));
        $slot2 = SlotMethod::fromClosure($receiver2->mySlot(...));
        $collection = new SlotCollection();
        $args = [1, 'string'];

        $collection->invoke($args);
        self::assertSame(0, $receiver1->nbCalls());
        self::assertSame(0, $receiver2->nbCalls());

        $collection->add($slot1);
        $collection->add($slot2);
        $collection->invoke($args);

        self::assertSame([$args], $receiver1->calls());
        self::assertSame([$args], $receiver2->calls());
    }

    public function testRemovedSlotsAreNotInvokedAnymore(): void
    {
        $receiver = new SpyReceiver();

        $slot = SlotMethod::fromClosure($receiver->mySlot(...));
        $collection = new SlotCollection();
        $args = [1, 'string'];

        $collection->add($slot);
        $collection->invoke($args);
        self::assertSame(1, $receiver->nbCalls());

        $collection->remove($slot);
        $collection->invoke($args);
        self::assertSame(1, $receiver->nbCalls());
    }

    public function testSlotCanBeRemovedFromAnOtherMethodInstance(): void
    {
        $receiver = new SpyReceiver();

        $slot1 = SlotMethod::fromClosure($receiver->mySlot(...));
        $slot2 = SlotMethod::fromClosure($receiver->mySlot(...));
        $collection = new SlotCollection();

        $collection->add($slot1);
        $collection->remove($slot2);
        self::assertSame(0, $receiver->nbCalls());
    }

    public function testSlotsAddedTwiceAreInvokedOnce(): void
    {
        $receiver = new SpyReceiver();

        $slot1 = SlotMethod::fromClosure($receiver->mySlot(...));
        $slot2 = SlotMethod::fromClosure($receiver->mySlot(...));
        $collection = new SlotCollection();
        $args = [1, 'string'];

        $collection->add($slot1);
        $collection->add($slot1);
        $collection->add($slot2);
        $collection->invoke($args);
        self::assertSame(1, $receiver->nbCalls());
    }

    public function testRemovingUnknownSlotThrowsAnException(): void
    {
        $receiver = new SpyReceiver();

        $slot = SlotMethod::fromClosure($receiver->mySlot(...));
        $collection = new SlotCollection();

        self::expectException(SiglotError::class);
        self::expectExceptionMessage('Slot not found in collection');
        $collection->remove($slot);
    }

    public function testSeveralSlotsFromSameReceiverAreAllowed(): void
    {
        $receiver = new class () {
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

        $slot1 = SlotMethod::fromClosure($receiver->mySlot1(...));
        $slot2 = SlotMethod::fromClosure($receiver->mySlot2(...));
        $collection = new SlotCollection();

        $collection->add($slot1);
        $collection->add($slot2);
        $collection->invoke(['input']);

        self::assertSame([['mySlot1', 'input'], ['mySlot2', 'input']], $receiver->calls);
    }

    public function testSlotsCanBeAddedDuringInvocation(): void
    {
        $receiver = new SpyReceiver();

        $receiverAddingSlot = new class () {
            public SlotMethod $slotToAdd;
            public int $nbCalls = 0;

            public function mySlot(SlotCollection $collection): void
            {
                ++$this->nbCalls;
                $collection->add($this->slotToAdd);
            }
        };

        $slot = SlotMethod::fromClosure($receiver->mySlot(...));
        $addSlot = SlotMethod::fromClosure($receiverAddingSlot->mySlot(...));
        $receiverAddingSlot->slotToAdd = $slot;

        $collection = new SlotCollection();
        $collection->add($addSlot);

        $collection->invoke([$collection]);

        self::assertSame(1, $receiverAddingSlot->nbCalls);
        self::assertSame(1, $receiver->nbCalls());
    }

    public function testSlotsCanBeRemovedDuringInvocation(): void
    {
        $receiver1 = new SpyReceiver();
        $receiver2 = new SpyReceiver();
        $receiver3 = new SpyReceiver();


        $receiverRemovingPreviousSlot = new class () {
            public SlotMethod $slotToRemove;
            public int $nbCalls = 0;

            public function mySlot(SlotCollection $collection): void
            {
                ++$this->nbCalls;
                $collection->remove($this->slotToRemove);
            }
        };
        $receiverRemoverNextSlot = new $receiverRemovingPreviousSlot();
        $receiverRemovingCurrentSlot = new $receiverRemovingPreviousSlot();

        $slot1 = SlotMethod::fromClosure($receiver1->mySlot(...));
        $slot2 = SlotMethod::fromClosure($receiver2->mySlot(...));
        $slot3 = SlotMethod::fromClosure($receiver3->mySlot(...));

        $removeSlot1 = SlotMethod::fromClosure($receiverRemovingPreviousSlot->mySlot(...));
        $receiverRemovingPreviousSlot->slotToRemove = $slot1;
        $removeSlot3 = SlotMethod::fromClosure($receiverRemoverNextSlot->mySlot(...));
        $receiverRemoverNextSlot->slotToRemove = $slot3;
        $removeItself = SlotMethod::fromClosure($receiverRemovingCurrentSlot->mySlot(...));
        $receiverRemovingCurrentSlot->slotToRemove = $removeItself;

        $collection = new SlotCollection();
        $collection->add($slot1);
        $collection->add($removeSlot1);
        $collection->add($removeItself);
        $collection->add($removeSlot3);
        $collection->add($slot2);
        $collection->add($slot3);

        $collection->invoke([$collection]);

        self::assertSame(1, $receiver1->nbCalls());
        self::assertSame(1, $receiverRemovingPreviousSlot->nbCalls);
        self::assertSame(1, $receiverRemovingCurrentSlot->nbCalls);
        self::assertSame(1, $receiverRemoverNextSlot->nbCalls);
        self::assertSame(1, $receiver2->nbCalls());
        self::assertSame(0, $receiver3->nbCalls());
    }
}
