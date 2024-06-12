<?php

declare(strict_types=1);

namespace Bviguier\Siglot\Internal;

use Bviguier\Siglot\SignalEvent;

final class SignalManager
{
    public function __construct()
    {
        $this->connections = new \ArrayObject();
    }

    public function connector(SignalMethod $signal): Connector
    {
        $connection = $this->connections[$signal->name]
            ?? $this->connections[$signal->name] = Connection::fromSignal($signal);

        return $connection->connector;
    }

    public function emit(SignalEvent $signal): void
    {
        $this->connections[$signal->method]?->invoke($signal->args);
    }

    /** @var \ArrayObject<string,Connection> $connections */
    private \ArrayObject $connections;
}
