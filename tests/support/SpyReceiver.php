<?php

declare(strict_types=1);

namespace Bviguier\Siglot\Tests\Support;

class SpyReceiver
{
    public function mySlot(): void
    {
        $this->calls[] = \func_get_args();
    }

    public function nbCalls(): int
    {
        return \count($this->calls);
    }

    /**
     * @return array<mixed[]>
     */
    public function calls(): array
    {
        return $this->calls;
    }
    /** @var array<mixed[]> */
    private array $calls = [];
}
