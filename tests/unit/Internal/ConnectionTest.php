<?php

declare(strict_types=1);

namespace Bviguier\Siglot\Tests\Unit\Internal;

use Bviguier\Siglot\Internal\Connection;
use Bviguier\Siglot\Internal\SignalMethod;
use Bviguier\Siglot\Internal\SlotMethod;
use Bviguier\Siglot\Tests\Support\SpyReceiver;
use Bviguier\Siglot\Tests\Support\TestEmitter;
use PHPUnit\Framework\TestCase;

class ConnectionTest extends TestCase
{
    public function testConnectionCanBeModifiedThroughItsConnector(): void
    {
        $connection = Connection::fromSignal(SignalMethod::fromClosure(
            (new TestEmitter())->mySignal(...)
        ));
        $slot = SlotMethod::fromClosure(
            ($receiver = new SpyReceiver())->mySlot(...),
        );

        $connection->connector->connect($slot);
        $connection->invoke([]);
        self::assertSame(1, $receiver->nbCalls());

        $connection->connector->disconnect($slot);
        $connection->invoke([]);
        self::assertSame(1, $receiver->nbCalls());
    }

    public function testConnectionsChainingAndUnchaining(): void
    {
        $connectionSrc = Connection::fromSignal(SignalMethod::fromClosure(
            (new TestEmitter())->mySignal(...)
        ));

        $connectionDst = Connection::fromSignal(SignalMethod::fromClosure(
            ($emitter = new TestEmitter())->mySignal(...)
        ));
        $connectionDst->connector->connect(SlotMethod::fromClosure(
            ($receiver = new SpyReceiver())->mySlot(...),
        ));

        $connectionSrc->connector->chain($connectionDst->connector);
        $connectionSrc->invoke([]);
        self::assertSame(1, $receiver->nbCalls());

        $connectionSrc->connector->unchain($connectionDst->connector);
        $connectionSrc->invoke([]);
        self::assertSame(1, $receiver->nbCalls());
    }
}
