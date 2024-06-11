<?php

declare(strict_types=1);

namespace Bviguier\Siglot\Internal;

final class Connector
{
    public function __construct(private SignalMethod $signal, private SlotCollection $slots) {}

    public function chain(self $other): void
    {
        $this->slots->add(
            SlotMethod::fromWrappedSignal($other->signal, $other->slots->invoke(...))
        );
    }

    public function unchain(self $other): void
    {
        $this->slots->remove(
            SlotMethod::fromWrappedSignal($other->signal, $other->slots->invoke(...))
        );
    }

    public function connect(SlotMethod $slot): void
    {
        $this->slots->add($slot);
    }

    public function disconnect(SlotMethod $slot): void
    {
        $this->slots->remove($slot);
    }
}
