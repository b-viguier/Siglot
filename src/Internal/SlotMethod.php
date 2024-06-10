<?php

declare(strict_types=1);

namespace Bviguier\Siglot\Internal;

use Bviguier\Siglot\SiglotError;

class SlotMethod
{
    public static function fromClosure(\Closure $closure): self
    {
        $reflection = new \ReflectionFunction($closure);
        $object = $reflection->getClosureThis();

        if ($object === null) {
            throw new SiglotError("Closure is not bound to an object");
        }

        $methodName = $reflection->getName();
        if (!(new \ReflectionClass($object))->hasMethod($methodName)) {
            throw new SiglotError("Attempt to create a Slot from unknown method");
        }

        return new self(
            $methodName,
            $object,
            fn() => $this->$methodName(...\func_get_args()),    // @phpstan-ignore-line
        );
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

    /**
     * @param array<mixed> $args
     */
    public function invoke(array $args): mixed
    {
        $instance = $this->object();

        return \call_user_func_array(
            $this->function->bindTo($instance, $instance::class),
            $args,
        );
    }

    /** @var \WeakReference<object> */
    private \WeakReference $object;

    /**
     * @param \Closure(mixed ...$params):mixed $function
     */
    private function __construct(
        public readonly string $name,
        object $object,
        private readonly \Closure $function,
    ) {
        $this->object = \WeakReference::create($object);

        $reflection = new \ReflectionFunction($function);

        \assert($reflection->getClosureThis() === null);
    }
}
