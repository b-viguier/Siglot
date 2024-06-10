<?php

declare(strict_types=1);

namespace Bviguier\Siglot\Internal;

use Bviguier\Siglot\Emitter;
use Bviguier\Siglot\SiglotError;
use Bviguier\Siglot\SignalEvent;

class SignalMethod
{
    public static function fromClosure(\Closure $closure): self
    {
        $reflection = new \ReflectionFunction($closure);
        $object = $reflection->getClosureThis();

        if ($object === null) {
            throw new SiglotError("Closure is not bound to an object");
        }
        if (!$object instanceof Emitter) {
            throw new SiglotError("Closure is not bound to an Emitter object");
        }

        $methodName = $reflection->getName();
        if (!(new \ReflectionClass($object))->hasMethod($methodName)) {
            throw new SiglotError("Attempt to create a Signal from unknown method");
        }

        $returnType = (new \ReflectionMethod($object, $methodName))->getReturnType();
        if (
            !($returnType  instanceof \ReflectionNamedType)
            || $returnType->getName() !== SignalEvent::class
            || $returnType->allowsNull()
        ) {
            throw new SiglotError("Closure does not return a SignalEvent object");
        }

        return new self(
            $methodName,
            $object,
            fn() => $this->$methodName(...\func_get_args()), // @phpstan-ignore-line
        );
    }


    /**
     * @param array<mixed> $args
     */
    public function invoke(array $args): SignalEvent
    {
        $instance = $this->object();

        $event = \call_user_func_array(
            $this->function->bindTo($instance, $instance::class),
            $args,
        );
        \assert($event instanceof SignalEvent);

        return $event;
    }

    public function object(): object
    {
        \assert($this->object->get() !== null);

        return $this->object->get();
    }

    public function isValid(): bool
    {
        return $this->object->get() !== null;
    }


    /** @var \WeakReference<object> */
    private \WeakReference $object;

    /**
     * @param \Closure(mixed ...$params):SignalEvent $function
     */
    private function __construct(
        public readonly string $name,
        object $object,
        private readonly \Closure $function,
    ) {
        \assert((new \ReflectionFunction($function))->getClosureThis() === null);

        $this->object = \WeakReference::create($object);
    }
}
