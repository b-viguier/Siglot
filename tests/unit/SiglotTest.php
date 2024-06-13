<?php

declare(strict_types=1);

namespace Bviguier\Siglot\Tests\Unit;

use Bviguier\Siglot\Siglot;
use Bviguier\Siglot\Tests\Support\SpyReceiver;
use Bviguier\Siglot\Tests\Support\TestEmitter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SiglotTest extends TestCase
{
    public function testConnect0(): void
    {
        $emitter = new TestEmitter();
        $receiver = new SpyReceiver();

        Siglot::connect0($emitter->mySignal(...), $receiver->mySlot(...));
        $emitter->emit($emitter->mySignal());

        self::assertSame(1, $receiver->nbCalls());
    }

    public function testChain0(): void
    {
        $emitter1 = new TestEmitter();
        $emitter2 = new TestEmitter();
        $receiver = new SpyReceiver();

        Siglot::chain0($emitter1->mySignal(...), $emitter2->mySignal(...));
        Siglot::connect0($emitter2->mySignal(...), $receiver->mySlot(...));
        $emitter1->emit($emitter1->mySignal());

        self::assertSame(1, $receiver->nbCalls());
    }

    #[DataProvider('provideNbParams')]
    public function testConnectN(int $nbEmitterParams, int $nbReceiverParams): void
    {
        $emitter = new TestEmitter();
        $receiver = new SpyReceiver();

        $connectMethod = "connect$nbEmitterParams";
        $signalMethod = "mySignal$nbEmitterParams";
        $slotMethod = "mySlot$nbReceiverParams";
        $emitterParams = $nbEmitterParams ? \range(1, $nbEmitterParams) : [];
        $receiverParams = $nbReceiverParams ? \range(1, $nbReceiverParams) : [];

        Siglot::$connectMethod($emitter->$signalMethod(...), $receiver->$slotMethod(...));
        $emitter->emit($emitter->$signalMethod(...$emitterParams));

        self::assertSame(1, $receiver->nbCalls($slotMethod));
        self::assertSame([$receiverParams], $receiver->calls($slotMethod));
    }

    #[DataProvider('provideNbParams')]
    public function testChainN(int $nbEmitterParams, int $nbReceiverParams): void
    {
        $emitter1 = new TestEmitter();
        $emitter2 = new TestEmitter();
        $receiver = new SpyReceiver();

        $connectMethod = "connect$nbEmitterParams";
        $chainMethod = "chain$nbEmitterParams";
        $signal1Method = "mySignal$nbEmitterParams";
        $signal2Method = "mySignal$nbReceiverParams";
        $slotMethod = "mySlot$nbReceiverParams";
        $emitterParams = $nbEmitterParams ? \range(1, $nbEmitterParams) : [];
        $receiverParams = $nbReceiverParams ? \range(1, $nbReceiverParams) : [];

        Siglot::$chainMethod($emitter1->$signal1Method(...), $emitter2->$signal2Method(...));
        Siglot::$connectMethod($emitter2->$signal2Method(...), $receiver->$slotMethod(...));
        $emitter1->emit($emitter1->$signal1Method(...$emitterParams));

        self::assertSame(1, $receiver->nbCalls($slotMethod));
        self::assertSame([$receiverParams], $receiver->calls($slotMethod));
    }

    /**
     * @return iterable<array{positive-int,int}>
     */
    public static function provideNbParams(): iterable
    {
        for ($nbEmitterParameters = 1; $nbEmitterParameters <= 5; ++$nbEmitterParameters) {
            for ($nbReceiverParameters = 0; $nbReceiverParameters <= $nbEmitterParameters; ++$nbReceiverParameters) {
                yield "$nbEmitterParameters => $nbReceiverParameters" => [$nbEmitterParameters, $nbReceiverParameters];
            }
        }
    }
}
