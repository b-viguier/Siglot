<?php

declare(strict_types=1);

namespace Bviguier\Siglot\Tests\Unit\Internal;

use Bviguier\Siglot\Emitter;
use Bviguier\Siglot\Internal\Connection;
use Bviguier\Siglot\Internal\SignalMethod;
use Bviguier\Siglot\Internal\SlotMethod;
use Bviguier\Siglot\SignalEvent;
use PHPUnit\Framework\TestCase;

class ConnectionTest extends TestCase
{
    public function testConnectionCanBeModifiedThroughItsConnector(): void
    {
        $connection = Connection::fromSignal(SignalMethod::fromClosure(
            (new class () implements Emitter {
                public function mySignal(): SignalEvent
                {
                    return SignalEvent::auto();
                }
            })->mySignal(...)
        ));
        $slot = SlotMethod::fromClosure(
            ($object = new class () {
                public int $nbCalls = 0;
                public function mySlot(): void
                {
                    ++$this->nbCalls;
                }
            })->mySlot(...),
        );

        $connection->connector->connect($slot);
        $connection->invoke([]);
        self::assertSame(1, $object->nbCalls);

        $connection->connector->disconnect($slot);
        $connection->invoke([]);
        self::assertSame(1, $object->nbCalls);
    }

    public function testConnectionsChainingAndUnchaining(): void
    {
        $connectionSrc = Connection::fromSignal(SignalMethod::fromClosure(
            (new class () implements Emitter {
                public function mySignal(): SignalEvent
                {
                    return SignalEvent::auto();
                }
            })->mySignal(...)
        ));

        $connectionDst = Connection::fromSignal(SignalMethod::fromClosure(
            ($signalObject = new class () implements Emitter {
                public function mySignal(): SignalEvent
                {
                    return SignalEvent::auto();
                }
            })->mySignal(...)
        ));
        $connectionDst->connector->connect(SlotMethod::fromClosure(
            ($object = new class () {
                public int $nbCalls = 0;
                public function mySlot(): void
                {
                    ++$this->nbCalls;
                }
            })->mySlot(...),
        ));

        $connectionSrc->connector->chain($connectionDst->connector);
        $connectionSrc->invoke([]);
        self::assertSame(1, $object->nbCalls);

        $connectionSrc->connector->unchain($connectionDst->connector);
        $connectionSrc->invoke([]);
        self::assertSame(1, $object->nbCalls);
    }
}
