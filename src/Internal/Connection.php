<?php

declare(strict_types=1);

namespace Bviguier\Siglot\Internal;

final class Connection
{
    public static function fromSignal(SignalMethod $signal): self
    {
        return new self(
            $slots = new SlotCollection(),
            new Connector($signal, $slots),
        );
    }

    /** @param mixed[] $args */
    public function invoke(array $args): void
    {
        $this->slots->invoke($args);
    }

    private function __construct(
        private readonly SlotCollection $slots,
        public readonly Connector $connector,
    ) {}
}
