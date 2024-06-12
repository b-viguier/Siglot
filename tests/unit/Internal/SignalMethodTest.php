<?php

declare(strict_types=1);

namespace Bviguier\Siglot\Tests\Unit\Internal;

use Bviguier\Siglot\Emitter;
use Bviguier\Siglot\Internal\SignalMethod;
use Bviguier\Siglot\SiglotError;
use Bviguier\Siglot\SignalEvent;
use Bviguier\Siglot\Tests\Support\FakeEmitterTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SignalMethodTest extends TestCase
{
    public function testSignalMethodCreation(): void
    {
        $object = new class () implements Emitter {
            use FakeEmitterTrait;
            public function mySignal(): SignalEvent
            {
                return SignalEvent::auto();
            }
        };

        $signalMethod = SignalMethod::fromClosure($object->mySignal(...));

        self::assertTrue($signalMethod->isValid());
        self::assertSame($object, $signalMethod->object());
        self::assertSame('mySignal', $signalMethod->name);
    }

    public function testCaseInsensitivity(): void
    {
        $object = new class () implements Emitter {
            use FakeEmitterTrait;
            public function mySignal(): SignalEvent
            {
                return SignalEvent::auto();
            }
        };

        $signalMethod = SignalMethod::fromClosure($object->MYSIGNAL(...));

        self::assertTrue($signalMethod->isValid());
        self::assertSame($object, $signalMethod->object());
        self::assertSame('mySignal', $signalMethod->name);
    }

    public function testItDoesNotPreventGarbageCollection(): void
    {
        $object = new class () implements Emitter {
            use FakeEmitterTrait;
            public function mySignal(): SignalEvent
            {
                return SignalEvent::auto();
            }
        };

        $signalMethod = SignalMethod::fromClosure($object->mySignal(...));

        self::assertTrue($signalMethod->isValid());

        unset($object);
        \gc_collect_cycles();

        self::assertFalse($signalMethod->isValid());
    }

    public function testInvocation(): void
    {
        $object = new class () implements Emitter {
            use FakeEmitterTrait;
            public function mySignal(int $int, string $string): SignalEvent
            {
                return SignalEvent::auto();
            }
        };

        $signalMethod = SignalMethod::fromClosure($object->mySignal(...));
        $event = $signalMethod->invoke([1, 'string']);

        self::assertSame($object, $event->object);
        self::assertSame('mySignal', $event->method);
        self::assertSame([1, 'string'], $event->args);
    }

    public function testCreationFromPrivateMethod(): void
    {
        $object = new class () implements Emitter {
            use FakeEmitterTrait;
            private function myPrivateSignal(string $string): SignalEvent
            {
                return SignalEvent::auto();
            }

            public function getSignalMethod(): SignalMethod
            {
                return SignalMethod::fromClosure($this->myPrivateSignal(...));
            }
        };

        $signalMethod = $object->getSignalMethod();
        $event = $signalMethod->invoke(['string']);

        self::assertSame($object, $event->object);
        self::assertSame('myPrivateSignal', $event->method);
        self::assertSame(['string'], $event->args);
    }

    public function testExceptionThrownWhenClosureIsNotBoundToObject(): void
    {
        self::expectException(SiglotError::class);
        self::expectExceptionMessage('Closure is not bound to an object');

        SignalMethod::fromClosure(static fn() => null);
    }

    public function testExceptionThrownWhenClosureIsNotBoundToEmitterObject(): void
    {
        self::expectException(SiglotError::class);
        self::expectExceptionMessage('Closure is not bound to an Emitter object');

        SignalMethod::fromClosure(fn() => null);
    }

    public function testExceptionThrownWhenMethodDoesNotExist(): void
    {
        self::expectException(SiglotError::class);
        self::expectExceptionMessage('Attempt to create a Signal from unknown method');

        $object = new class () implements Emitter {
            use FakeEmitterTrait;
            public function createClosure(): \Closure
            {
                return fn() => null;
            }
        };

        SignalMethod::fromClosure($object->createClosure());
    }

    #[DataProvider('invalidSignalMethodProvider')]
    public function testExceptionThrownWhenClosureDoesNotReturnsSignalEventObject(object $object): void
    {
        self::expectException(SiglotError::class);
        self::expectExceptionMessage('Closure does not return a SignalEvent object');

        SignalMethod::fromClosure($object->mySignal(...)); // @phpstan-ignore-line
    }

    /**
     * @return iterable<string,array{0:object}>
     */
    public static function invalidSignalMethodProvider(): iterable
    {
        yield 'Closure does not return a SignalEvent object' => [
            new class () implements Emitter {
                use FakeEmitterTrait;
                public function mySignal(): void {}
            },
        ];

        yield 'Closure returns nullable type' => [
            new class () implements Emitter {
                use FakeEmitterTrait;
                public function mySignal(): SignalEvent|null
                {
                    return null;
                }
            },
        ];

        yield 'Closure returns union' => [
            new class () implements Emitter {
                use FakeEmitterTrait;
                public function mySignal(): SignalEvent|int
                {
                    return 2;
                }
            },
        ];
    }
}
