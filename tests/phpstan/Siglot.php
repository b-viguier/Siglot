<?php

declare(strict_types=1);

use Bviguier\Siglot\Emitter;
use Bviguier\Siglot\Siglot;
use Bviguier\Siglot\SignalEvent;
use Bviguier\Siglot\Tests\Support\FakeEmitterTrait;
use Bviguier\Siglot\Tests\Support\SpyReceiver;
use Bviguier\Siglot\Tests\Support\TestEmitter;

$emitter = new TestEmitter();
$receiver = new SpyReceiver();

// Match number of parameters ✅
Siglot::connect0(
    $emitter->mySignal0(...),
    $receiver->mySlot0(...),
);

// Receiver expects less parameters ✅
Siglot::connect1(
    $emitter->mySignal1(...),
    $receiver->mySlot0(...),
);

// Receiver expects more parameters ❌
Siglot::connect0(
    $emitter->mySignal0(...),
    $receiver->mySlot1(...),    // @phpstan-ignore argument.type
);

// Connection expects more parameters ✅
Siglot::connect1(
    $emitter->mySignal0(...),
    $receiver->mySlot0(...),
);

// Connection expects less parameters ❌
Siglot::connect0(
    $emitter->mySignal1(...),   // @phpstan-ignore argument.type
    $receiver->mySlot1(...),    // @phpstan-ignore argument.type
);

// Cannot connect from Slot ❌
Siglot::connect0(
    $receiver->mySlot0(...),    // @phpstan-ignore argument.type
    $receiver->mySlot0(...),
);

interface ChildInterface {}
class ParentClass {}
class ChildClass extends ParentClass implements ChildInterface {}

class OtherClass {}

$emitter = new class() implements Emitter {
    use FakeEmitterTrait;

    public function string(string $string): SignalEvent
    {
        return SignalEvent::auto();
    }

    public function parentClass(ParentClass $parentClass): SignalEvent
    {
        return SignalEvent::auto();
    }

    public function childClass(ChildClass $childClass): SignalEvent
    {
        return SignalEvent::auto();
    }

    public function childInterface(ChildInterface $childInterface): SignalEvent
    {
        return SignalEvent::auto();
    }

    public function otherClass(OtherClass $otherClass): SignalEvent
    {
        return SignalEvent::auto();
    }
};

$receiver = new class() {
    public function int(int $int): void {}

    public function parentClass(ParentClass $parentClass): void {}

    public function childClass(ChildClass $childClass): void {}

    public function childInterface(ChildInterface $childInterface): void {}

    public function otherClass(OtherClass $otherClass): void {}
};

// Incompatible types ❌
Siglot::connect1(
    $emitter->string(...),
    $receiver->int(...),    // @phpstan-ignore argument.type
);

// Incompatible types ❌
Siglot::connect1(
    $emitter->otherClass(...),
    $receiver->childClass(...),    // @phpstan-ignore argument.type
);

// Incompatible types ❌
Siglot::connect1(
    $emitter->childClass(...),
    $receiver->otherClass(...),    // @phpstan-ignore argument.type
);

// Signal type extends slot type ✅
Siglot::connect1(
    $emitter->childClass(...),
    $receiver->parentClass(...),
);

// Signal type is a parent of slot type (⚠️ shouldn't work)
Siglot::connect1(
    $emitter->parentClass(...),
    $receiver->childClass(...),
);

// Signal type implements slot type ✅
Siglot::connect1(
    $emitter->childClass(...),
    $receiver->childInterface(...),
);

// Interface can be matched with any object (⚠️ shouldn't work)
Siglot::connect1(
    $emitter->childInterface(...),
    $receiver->otherClass(...),
);
