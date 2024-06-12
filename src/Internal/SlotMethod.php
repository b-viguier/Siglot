<?php

declare(strict_types=1);

namespace Bviguier\Siglot\Internal;

use Bviguier\Siglot\SiglotError;

final class SlotMethod
{
    public static function fromClosure(\Closure $closure): self
    {
        $reflection = new \ReflectionFunction($closure);
        $receiver = $reflection->getClosureThis();

        if ($receiver === null) {
            throw new SiglotError("Closure is not bound to an object");
        }

        $methodName = $reflection->getName();
        if (!(new \ReflectionClass($receiver))->hasMethod($methodName)) {
            throw new SiglotError("Attempt to create a Slot from unknown method");
        }

        return new self(
            $methodName,
            $receiver,
            fn() => $this->$methodName(...\func_get_args()),    // @phpstan-ignore-line
        );
    }

    public static function fromWrappedSignal(SignalMethod $signalMethod, \Closure $wrapper): self
    {
        return new self(
            $signalMethod->name,
            $signalMethod->emitter(),
            fn() => $wrapper($signalMethod->invoke(\func_get_args())->args),
        );
    }

    public function receiver(): object
    {
        \assert($this->receiver->get() !== null);

        return $this->receiver->get();
    }

    public function isValid(): bool
    {
        return $this->receiver->get() !== null;
    }

    /**
     * @param array<mixed> $args
     */
    public function invoke(array $args): mixed
    {
        $instance = $this->receiver();

        return \call_user_func_array(
            $this->function->bindTo($instance, $instance::class),
            $args,
        );
    }

    /** @var \WeakReference<object> */
    private \WeakReference $receiver;

    /**
     * @param \Closure(mixed ...$params):mixed $function
     */
    private function __construct(
        public readonly string $name,
        object $receiver,
        private readonly \Closure $function,
    ) {
        $this->receiver = \WeakReference::create($receiver);

        $reflection = new \ReflectionFunction($function);

        \assert($reflection->getClosureThis() === null);
    }
}
