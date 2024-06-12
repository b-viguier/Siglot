<?php

declare(strict_types=1);

namespace Bviguier\Siglot;

use Bviguier\Siglot\Internal\Connector;
use Bviguier\Siglot\Internal\SignalMethod;

interface Emitter
{
    public function connector(SignalMethod $signal): Connector;
}
