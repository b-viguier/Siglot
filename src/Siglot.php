<?php

declare(strict_types=1);

namespace Bviguier\Siglot;

class Siglot
{
    /**
     * @param \Closure():SignalEvent $signal
     * @param \Closure():mixed $slot
     */
    public static function connect0(\Closure $signal, \Closure $slot): void
    {
        self::connect($signal, $slot);
    }

    /**
     * @template T1
     * @param \Closure(T1):SignalEvent $signal
     * @param (\Closure(T1):mixed)|(\Closure():mixed) $slot
     */
    public static function connect1(\Closure $signal, \Closure $slot): void
    {
        self::connect($signal, $slot);
    }

    /**
     * @template T1
     * @template T2
     * @param \Closure(T1,T2):SignalEvent $signal
     * @param (\Closure(T1,T2):mixed)|(\Closure(T1):mixed)|(\Closure():mixed) $slot
     */
    public static function connect2(\Closure $signal, \Closure $slot): void
    {
        self::connect($signal, $slot);
    }

    /**
     * @template T1
     * @template T2
     * @template T3
     * @param \Closure(T1,T2,T3):SignalEvent $signal
     * @param (\Closure(T1,T2,T3):mixed)|(\Closure(T1,T2):mixed)|(\Closure(T1):mixed)|(\Closure():mixed) $slot
     */
    public static function connect3(\Closure $signal, \Closure $slot): void
    {
        self::connect($signal, $slot);
    }

    /**
     * @template T1
     * @template T2
     * @template T3
     * @template T4
     * @param \Closure(T1,T2,T3,T4):SignalEvent $signal
     * @param (\Closure(T1,T2,T3,T4):mixed)|(\Closure(T1,T2,T3):mixed)|(\Closure(T1,T2):mixed)|(\Closure(T1):mixed)|(\Closure():mixed) $slot
     */
    public static function connect4(\Closure $signal, \Closure $slot): void
    {
        self::connect($signal, $slot);
    }

    /**
     * @template T1
     * @template T2
     * @template T3
     * @template T4
     * @template T5
     * @param \Closure(T1,T2,T3,T4,T5):SignalEvent $signal
     * @param (\Closure(T1,T2,T3,T4,T5):mixed)|(\Closure(T1,T2,T3,T4):mixed)|(\Closure(T1,T2,T3):mixed)|(\Closure(T1,T2):mixed)|(\Closure(T1):mixed)|(\Closure():mixed) $slot
     */
    public static function connect5(\Closure $signal, \Closure $slot): void
    {
        self::connect($signal, $slot);
    }

    /**
     * @param \Closure():SignalEvent $signal
     * @param \Closure():SignalEvent $slotSignal
     */
    public static function chain0(\Closure $signal, \Closure $slotSignal): void
    {
        self::chain($signal, $slotSignal);
    }

    /**
     * @template T1
     * @param \Closure(T1):SignalEvent $signal
     * @param (\Closure(T1):SignalEvent)|(\Closure():SignalEvent) $slotSignal
     */
    public static function chain1(\Closure $signal, \Closure $slotSignal): void
    {
        self::chain($signal, $slotSignal);
    }

    /**
     * @template T1
     * @template T2
     * @param \Closure(T1,T2):SignalEvent $signal
     * @param (\Closure(T1,T2):SignalEvent)|(\Closure(T1):SignalEvent)|(\Closure():SignalEvent) $slotSignal
     */
    public static function chain2(\Closure $signal, \Closure $slotSignal): void
    {
        self::chain($signal, $slotSignal);
    }

    /**
     * @template T1
     * @template T2
     * @template T3
     * @param \Closure(T1,T2,T3):SignalEvent $signal
     * @param (\Closure(T1,T2,T3):SignalEvent)|(\Closure(T1,T2):SignalEvent)|(\Closure(T1):SignalEvent)|(\Closure():SignalEvent) $slotSignal
     */
    public static function chain3(\Closure $signal, \Closure $slotSignal): void
    {
        self::chain($signal, $slotSignal);
    }

    /**
     * @template T1
     * @template T2
     * @template T3
     * @template T4
     * @param \Closure(T1,T2,T3,T4):SignalEvent $signal
     * @param (\Closure(T1,T2,T3,T4):SignalEvent)|(\Closure(T1,T2,T3):SignalEvent)|(\Closure(T1,T2):SignalEvent)|(\Closure(T1):SignalEvent)|(\Closure():SignalEvent) $slotSignal
     */
    public static function chain4(\Closure $signal, \Closure $slotSignal): void
    {
        self::chain($signal, $slotSignal);
    }

    /**
     * @template T1
     * @template T2
     * @template T3
     * @template T4
     * @template T5
     * @param \Closure(T1,T2,T3,T4,T5):SignalEvent $signal
     * @param (\Closure(T1,T2,T3,T4,T5):SignalEvent)|(\Closure(T1,T2,T3,T4):SignalEvent)|(\Closure(T1,T2,T3):SignalEvent)|(\Closure(T1,T2):SignalEvent)|(\Closure(T1):SignalEvent)|(\Closure():SignalEvent) $slotSignal
     */
    public static function chain5(\Closure $signal, \Closure $slotSignal): void
    {
        self::chain($signal, $slotSignal);
    }

    private static function connect(\Closure $signal, \Closure $slot): void
    {
        $signalMethod = Internal\SignalMethod::fromClosure($signal);

        $signalMethod->emitter()->connector($signalMethod)
            ->connect(Internal\SlotMethod::fromClosure($slot));
    }

    private static function chain(\Closure $srcSignal, \Closure $dstSignal): void
    {
        $srcMethod = Internal\SignalMethod::fromClosure($srcSignal);
        $dstMethod = Internal\SignalMethod::fromClosure($dstSignal);

        $srcMethod->emitter()->connector($srcMethod)
            ->chain(
                $dstMethod->emitter()->connector($dstMethod)
            );
    }
}
