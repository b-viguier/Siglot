<?php

declare(strict_types=1);

namespace Bviguier\Siglot\Tests\Unit\Internal;

use Bviguier\Siglot\Internal\Connector;
use Bviguier\Siglot\Internal\SignalMethod;
use Bviguier\Siglot\Internal\SlotCollection;
use Bviguier\Siglot\Internal\SlotMethod;
use Bviguier\Siglot\Tests\Support\SpyReceiver;
use Bviguier\Siglot\Tests\Support\TestEmitter;
use PHPUnit\Framework\TestCase;

class ConnectorTest extends TestCase
{
    public function testConnectedSlotIsAddedToCollection(): void
    {
        $signal = SignalMethod::fromClosure(
            (new TestEmitter())->mySignal(...)
        );
        $collection = new SlotCollection();
        $slot = SlotMethod::fromClosure(
            ($receiver = new SpyReceiver())->mySlot(...),
        );

        $connector = new Connector($signal, $collection);

        $connector->connect($slot);
        $collection->invoke([]);

        self::assertSame(1, $receiver->nbCalls());
    }

    public function testDisconnectSlotIsRemovedFromCollection(): void
    {
        $signal = SignalMethod::fromClosure(
            (new TestEmitter())->mySignal(...)
        );
        $collection = new SlotCollection();
        $slot = SlotMethod::fromClosure(
            ($receiver = new SpyReceiver())->mySlot(...),
        );

        $connector = new Connector($signal, $collection);

        $connector->connect($slot);
        $connector->disconnect($slot);
        $collection->invoke([]);

        self::assertSame(0, $receiver->nbCalls());
    }

    public function testConnectorsChainingAndUnchaining(): void
    {
        $signalSrc = SignalMethod::fromClosure(
            (new TestEmitter())->mySignal(...)
        );
        $collectionSrc = new SlotCollection();
        $connectorSrc = new Connector($signalSrc, $collectionSrc);

        $signalDst = SignalMethod::fromClosure(
            ($emitterDst = new TestEmitter())->mySignal(...)
        );
        $collectionDst = new SlotCollection();
        $collectionDst->add(SlotMethod::fromClosure(
            ($receiver = new SpyReceiver())->mySlot(...),
        ));
        $connectorDst = new Connector($signalDst, $collectionDst);

        $connectorSrc->chain($connectorDst);
        $collectionSrc->invoke([]);

        self::assertSame(1, $receiver->nbCalls());

        $connectorSrc->unchain($connectorDst);
        $collectionSrc->invoke([]);

        self::assertSame(1, $receiver->nbCalls());
    }
}
