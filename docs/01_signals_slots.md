---
title: Signals & Slots
nav_order: 1
permalink: /signals_slots
---

# Signals and Slots
{: .no_toc }

* TOC
{:toc}

## Signals

To define _signals_, a class must implement the
[`Emitter`](https://github.com/b-viguier/Siglot/blob/main/src/Emitter.php) interface.
All non-static methods that return a [`SignalEvent`](https://github.com/b-viguier/Siglot/blob/main/src/SignalEvent.php)
instance are considered as _signals_.

```php
// A class able to emit signals
class MyEmitter implements Emitter
{
    // A valid signal function
    public function signal(string $param1, int $param2): SignalEvent
    {
        return SignalEvent::auto();
    }
    
    // ... 
}
```

The goal of a  [`SignalEvent`](https://github.com/b-viguier/Siglot/blob/main/src/SignalEvent.php) instance
is to encapsulate all input parameters of the signal in a single object.
The static method `SignalEvent::auto()` uses reflection to facilitate parameter forwarding, thus preventing misordering of parameters.

{: .warning }
A signal function SHOULD only return a [`SignalEvent`](https://github.com/b-viguier/Siglot/blob/main/src/SignalEvent.php)
instance and SHOULD NOT perform any other actions.

We recommend using the [`EmitterHelper`](https://github.com/b-viguier/Siglot/blob/main/src/EmitterHelper.php)
trait to implement the [`Emitter`](https://github.com/b-viguier/Siglot/blob/main/src/Emitter.php) interface.
This trait provides the useful `emit(SignalEvent $signalEvent): void` method.
The `emit` function is `protected`,
by design, as it's best to only emit signals from the class that defines them and its subclasses.
You can find more details in the [Advanced]({{ site.baseurl }}{% link 03_advanced.md %}) section.

```php
class MyEmitter implements Emitter
{
    // ...
    public function processing(): void
    {
        // ...
        $this->emit(
            $this->signal('my string', 123)
        );
        // ...
    }
}
```


{: .warning }
Calling a signal function is not sufficient to actually trigger the signal;
the returned [`SignalEvent`](https://github.com/b-viguier/Siglot/blob/main/src/SignalEvent.php)
instance SHOULD be **immediately** _emitted_ using the `emit` method.

{: .good_to_know }
> * A class can define multiple signals.
> * There are no restrictions on the type of parameters a signal can have.
> * Although there are also no restrictions on the number of parameters,
> it is recommended to keep it low in order to be compatible with existing connection functions (see [Connections]({{ site.baseurl }}{% link 02_connections.md %})).
> * The visibility of a signal function only affects its capability to be called or connected,
  following the usual rules of visibility in PHP (see [Connections Visibility]({{ site.baseurl }}{% link 02_connections.md %}#visibility)).

## Slots
Any non-static object method can be considered a slot.
In most cases, it makes sense to call a slot as a regular method, without being triggered by a signal.

```php
class MyReceiver
{
    // A valid slot function
    public function slot(string $param1, int $param2): void
    {
        // ...
    }
    
    // ...
}
```

{: .good_to_know }
> * There are no restrictions on the type of parameters a slot can have.
> * Although there are also no restrictions on the number of parameters,
> it is recommended to keep it low in order to be compatible with existing connection functions (see [Connections]({{ site.baseurl }}{% link 02_connections.md %})).
> * The visibility of a slot function only affects its capability to be called or connected,
> following the usual rules of visibility in PHP (see [Connections Visibility]({{ site.baseurl }}{% link 02_connections.md %}#visibility)).
> * A slot SHOULD NOT return a value, as there is no way to retrieve it from the signal emitter (see [Connections]({{ site.baseurl }}{% link 02_connections.md %})).

