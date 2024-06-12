<?php

declare(strict_types=1);

namespace Bviguier\Siglot\Tests\Support;

use Bviguier\Siglot\Internal\Connector;
use Bviguier\Siglot\Internal\SignalMethod;

trait FakeEmitterTrait
{
    public function connector(SignalMethod $signal): Connector
    {
        throw new \RuntimeException("Unexpected call to TestEmitter:connector");
    }
}
