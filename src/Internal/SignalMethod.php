<?php

declare(strict_types=1);

namespace Bviguier\Siglot\Internal;

use Bviguier\Siglot\Emitter;
use Bviguier\Siglot\SiglotError;
use Bviguier\Siglot\SignalEvent;

final class SignalMethod
{
    public static function fromClosure(\Closure $closure): self
    {
        $reflection = new \ReflectionFunction($closure);
        $emitter = $reflection->getClosureThis();

        if ($emitter === null) {
            throw new SiglotError("Closure is not bound to an object");
        }
        if (!$emitter instanceof Emitter) {
            throw new SiglotError("Closure is not bound to an Emitter object");
        }

        $methodName = $reflection->getName();
        if (!(new \ReflectionClass($emitter))->hasMethod($methodName)) {
            throw new SiglotError("Attempt to create a Signal from unknown method");
        }

        $returnType = (new \ReflectionMethod($emitter, $methodName))->getReturnType();
        if (
            !($returnType  instanceof \ReflectionNamedType)
            || $returnType->getName() !== SignalEvent::class
            || $returnType->allowsNull()
        ) {
            throw new SiglotError("Closure does not return a SignalEvent object");
        }

        return new self(
            $methodName,
            $emitter,
            fn() => $this->$methodName(...\func_get_args()), // @phpstan-ignore-line
        );
    }


    /**
     * @param array<mixed> $args
     */
    public function invoke(array $args): SignalEvent
    {
        $instance = $this->emitter();

        $event = \call_user_func_array(
            $this->function->bindTo($instance, $instance::class),
            $args,
        );
        \assert($event instanceof SignalEvent);

        return $event;
    }

    public function emitter(): Emitter
    {
        \assert($this->emitter->get() !== null);

        return $this->emitter->get();
    }

    public function isValid(): bool
    {
        return $this->emitter->get() !== null;
    }


    /** @var \WeakReference<Emitter> */
    private \WeakReference $emitter;

    /**
     * @param \Closure(mixed ...$params):SignalEvent $function
     */
    private function __construct(
        public readonly string $name,
        Emitter $emitter,
        private readonly \Closure $function,
    ) {
        \assert((new \ReflectionFunction($function))->getClosureThis() === null);

        $this->emitter = \WeakReference::create($emitter);
    }
}
