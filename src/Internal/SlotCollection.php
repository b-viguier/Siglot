<?php

declare(strict_types=1);

namespace Bviguier\Siglot\Internal;

use Bviguier\Siglot\SiglotError;

final class SlotCollection
{
    public function __construct()
    {
        $this->slotInstances = new \WeakMap();
    }

    public function add(SlotMethod $slotMethod): void
    {
        \assert($slotMethod->isValid());
        $slotInstance = $this->slotInstances[$slotMethod->receiver()] ?? $this->slotInstances[$slotMethod->receiver()] = new \ArrayObject();

        $slotInstance[$slotMethod->name] = $slotMethod;
    }

    public function remove(SlotMethod $slotMethod): void
    {
        \assert($slotMethod->isValid());
        if (!isset($this->slotInstances[$slotMethod->receiver()])) {
            throw new SiglotError('Slot not found in collection');
        }

        unset($this->slotInstances[$slotMethod->receiver()][$slotMethod->name]);
    }

    /**
     * @param array<mixed> $args
     */
    public function invoke(array $args): void
    {
        foreach ($this->slotInstances as $slotInstance) {
            foreach ($slotInstance as $slotMethod) {
                $slotMethod->invoke($args);
            }
        }
    }
    /** @var \WeakMap<object, \ArrayObject<string,SlotMethod>> */
    private \WeakMap $slotInstances;
}
