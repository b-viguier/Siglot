<?php

declare(strict_types=1);

namespace Bviguier\Siglot;

final class SignalEvent
{
    public static function auto(): self
    {
        $backtrace = \debug_backtrace(\DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
        $entry = $backtrace[1] ?? null;
        if ($entry === null) {
            throw new SiglotError("Attempt to create signal outside of a function");
        }

        if (!isset($entry['object'])) {
            throw new SiglotError("Attempt to create signal outside of a method");
        }

        if (!(new \ReflectionClass($entry['object']))->hasMethod($entry['function'])) {
            throw new SiglotError("Attempt to create signal from unknown method");
        }

        return new self($entry['object'], $entry['function'], $entry['args'] ?? []);
    }
    /**
     * @param array<mixed> $args
     */
    private function __construct(
        public readonly object $object,
        public readonly string $method,
        public readonly array $args,
    ) {}
}
