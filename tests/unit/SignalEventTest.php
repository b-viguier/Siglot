<?php

declare(strict_types=1);

namespace Bviguier\Siglot\Tests\Unit;

use Bviguier\Siglot\SiglotError;
use Bviguier\Siglot\SignalEvent;
use PHPUnit\Framework\TestCase;

class SignalEventTest extends TestCase
{
    public function testAutoCreation(): void
    {
        $object = new class () {
            /** @param mixed[] $array */
            public function myMethod(int $int, string $string, array $array, object $obj): SignalEvent
            {
                return SignalEvent::auto();
            }
        };
        $args  = [1, 'string', ['array'], new \stdClass()];
        $method = 'myMethod';

        $event = $object->$method(...$args);

        self::assertSame($object, $event->object);
        self::assertSame($method, $event->method);
        self::assertSame($args, $event->args);

        // Case insensitivity
        $event = $object->MYMETHOD(...$args);
        self::assertSame($object, $event->object);
        self::assertSame($method, $event->method);
        self::assertSame($args, $event->args);
    }

    public function testAutoCreationDoesNotHandleReferences(): void
    {
        $object = new class () {
            public function myMethod(int &$int): SignalEvent
            {
                return SignalEvent::auto();
            }
        };
        $int = 1;

        $event = $object->myMethod($int);
        $int = 2;

        self::assertSame(1, $event->args[0]);
    }

    public function testAutoCreationFromStaticFunctionThrowsAnError(): void
    {
        $object = new class () {
            public static function myMethod(): SignalEvent
            {
                return SignalEvent::auto();
            }
        };

        self::expectException(SiglotError::class);
        self::expectExceptionMessage("Attempt to create signal outside of a method");
        $object::myMethod();
    }

    public function testAutoCreationFromAnonymousClosureThrowsAnError(): void
    {
        self::expectException(SiglotError::class);
        self::expectExceptionMessage("Attempt to create signal from unknown method");
        (fn() => SignalEvent::auto())();
    }

    public function testAutoCreationFromFirstClassCallableSyntaxIsHandled(): void
    {
        $object = new class () {
            public function myMethod(int $int): SignalEvent
            {
                return SignalEvent::auto();
            }
        };

        $closure = $object->myMethod(...);
        $event = $closure(1);

        self::assertSame($object, $event->object);
        self::assertSame('myMethod', $event->method);
        self::assertSame([1], $event->args);
    }

    public function testDefaultValuesAreNotHandled(): void
    {
        $object = new class () {
            /** @param mixed[] $array */
            public function myMethod(int $int = 1, string $string = 'string', array $array = ['array'], object $obj = null): SignalEvent
            {
                return SignalEvent::auto();
            }
        };
        $event = $object->myMethod();

        self::assertSame([], $event->args);
    }

    public function testArgsArePositional(): void
    {
        $object = new class () {
            public function myMethod(int $int, string $string, object $obj): SignalEvent
            {
                return SignalEvent::auto();
            }
        };
        $args = [
            'int' => 2,
            'string' => 'other',
            'obj' => new \stdClass(),
        ];
        $event = $object->myMethod(
            string: $args['string'],
            int: $args['int'],
            obj: $args['obj'],
        );

        self::assertSame(\array_values($args), $event->args);
    }

    public function testVariadicArgumentsAreHandled(): void
    {
        $object = new class () {
            public function myMethod(string $string, int ...$variadic): SignalEvent
            {
                return SignalEvent::auto();
            }
        };
        $args = ['string', 1, 2, 3, 4, 5];
        $event = $object->myMethod(... $args);

        self::assertSame($args, $event->args);
    }

    public function testExtraArgumentsAreHandled(): void
    {
        $object = new class () {
            public function myMethod(int $int, string $string): SignalEvent
            {
                return SignalEvent::auto();
            }
        };
        $args = [1, 'string', 'extra'];
        $event = $object->myMethod(...$args); // @phpstan-ignore-line

        self::assertSame($args, $event->args);
    }

    public function testAutoCreationFromPrivateMethod(): void
    {
        $object = new class () {
            private function myMethod(int ...$args): SignalEvent
            {
                return SignalEvent::auto();
            }

            public function callMyMethod(int ...$args): SignalEvent
            {
                return $this->myMethod(...$args);
            }
        };

        $args = [1, 2, 3];
        $event = $object->callMyMethod(...$args);

        self::assertSame($object, $event->object);
        self::assertSame('myMethod', $event->method);
        self::assertSame($args, $event->args);
    }
}
