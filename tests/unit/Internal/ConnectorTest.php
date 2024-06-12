<?php

declare(strict_types=1);

namespace Bviguier\Siglot\Tests\Unit\Internal;

use Bviguier\Siglot\Emitter;
use Bviguier\Siglot\Internal\Connector;
use Bviguier\Siglot\Internal\SignalMethod;
use Bviguier\Siglot\Internal\SlotCollection;
use Bviguier\Siglot\Internal\SlotMethod;
use Bviguier\Siglot\SignalEvent;
use Bviguier\Siglot\Tests\Support\FakeEmitterTrait;
use PHPUnit\Framework\TestCase;

class ConnectorTest extends TestCase
{
    public function testConnectedSlotIsAddedToCollection(): void
    {
        $signal = SignalMethod::fromClosure(
            (new class () implements Emitter {
                use FakeEmitterTrait;
                public function mySignal(): SignalEvent
                {
                    return SignalEvent::auto();
                }
            })->mySignal(...)
        );
        $collection = new SlotCollection();
        $slot = SlotMethod::fromClosure(
            ($object = new class () {
                public int $nbCalls = 0;
                public function mySlot(): void
                {
                    ++$this->nbCalls;
                }
            })->mySlot(...),
        );

        $connector = new Connector($signal, $collection);

        $connector->connect($slot);
        $collection->invoke([]);

        self::assertSame(1, $object->nbCalls);
    }

    public function testDisconnectSlotIsRemovedFromCollection(): void
    {
        $signal = SignalMethod::fromClosure(
            (new class () implements Emitter {
                use FakeEmitterTrait;
                public function mySignal(): SignalEvent
                {
                    return SignalEvent::auto();
                }
            })->mySignal(...)
        );
        $collection = new SlotCollection();
        $slot = SlotMethod::fromClosure(
            ($object = new class () {
                public int $nbCalls = 0;
                public function mySlot(): void
                {
                    ++$this->nbCalls;
                }
            })->mySlot(...),
        );

        $connector = new Connector($signal, $collection);

        $connector->connect($slot);
        $connector->disconnect($slot);
        $collection->invoke([]);

        self::assertSame(0, $object->nbCalls);
    }

    public function testConnectorsChainingAndUnchaining(): void
    {
        $signalSrc = SignalMethod::fromClosure(
            (new class () implements Emitter {
                use FakeEmitterTrait;
                public function mySignal(): SignalEvent
                {
                    return SignalEvent::auto();
                }
            })->mySignal(...)
        );
        $collectionSrc = new SlotCollection();
        $connectorSrc = new Connector($signalSrc, $collectionSrc);

        $signalDst = SignalMethod::fromClosure(
            ($signalObject = new class () implements Emitter {
                use FakeEmitterTrait;
                public function mySignal(): SignalEvent
                {
                    return SignalEvent::auto();
                }
            })->mySignal(...)
        );
        $collectionDst = new SlotCollection();
        $collectionDst->add(SlotMethod::fromClosure(
            ($object = new class () {
                public int $nbCalls = 0;
                public function mySlot(): void
                {
                    ++$this->nbCalls;
                }
            })->mySlot(...),
        ));
        $connectorDst = new Connector($signalDst, $collectionDst);

        $connectorSrc->chain($connectorDst);
        $collectionSrc->invoke([]);

        self::assertSame(1, $object->nbCalls);

        $connectorSrc->unchain($connectorDst);
        $collectionSrc->invoke([]);

        self::assertSame(1, $object->nbCalls);
    }
}
