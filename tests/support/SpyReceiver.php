<?php

declare(strict_types=1);

namespace Bviguier\Siglot\Tests\Support;

class SpyReceiver
{
    public function mySlot(): void
    {
        $this->calls[__FUNCTION__][] = \func_get_args();
    }

    public function mySlot0(): void
    {
        $this->calls[__FUNCTION__][] = [];
    }

    public function mySlot1(int $p1): void
    {
        $this->calls[__FUNCTION__][] = [$p1];
    }

    public function mySlot2(int $p1, int $p2): void
    {
        $this->calls[__FUNCTION__][] = [$p1, $p2];
    }

    public function mySlot3(int $p1, int $p2, int $p3): void
    {
        $this->calls[__FUNCTION__][] = [$p1, $p2, $p3];
    }

    public function mySlot4(int $p1, int $p2, int $p3, int $p4): void
    {
        $this->calls[__FUNCTION__][] = [$p1, $p2, $p3, $p4];
    }

    public function mySlot5(int $p1, int $p2, int $p3, int $p4, int $p5): void
    {
        $this->calls[__FUNCTION__][] = [$p1, $p2, $p3, $p4, $p5];
    }

    public function nbCalls(string $slotName = 'mySlot'): int
    {
        return \count($this->calls[$slotName] ?? []);
    }

    /**
     * @return array<mixed[]>
     */
    public function calls(string $slotName = 'mySlot'): array
    {
        return $this->calls[$slotName] ?? [];
    }
    /** @var array<string,array<mixed[]>> */
    private array $calls = [];
}
