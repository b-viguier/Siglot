---
title: Connections
nav_order: 2
permalink: /connections
---

# Connections
{: .no_toc }

* TOC
{:toc}

## Connecting signals to slots

The [`Siglot`](https://github.com/b-viguier/Siglot/blob/main/src/Siglot.php)
class offers various static methods for connecting a _signal_ to a _slot_.
These methods are named `connect<N>`, where `<N>` represents the number of parameters of the _signal_.
The numbering starts from `connect0` for signals without parameters and goes up to `connect5`.
In all cases, the first parameter is the _signal_ and the second is the _slot_.
Both are referenced using [first-class callable syntax](https://www.php.net/manual/en/functions.first_class_callable_syntax.php).

```php
Siglot::connect0($button->clicked(...), $receiver->onClick(...));
Siglot::connect1($editor->nameChanged(...), $receiver->onNewName(...));
```

{: .warning }
It is **highly** discouraged to use something else than
[first-class callable syntax](https://www.php.net/manual/en/functions.first_class_callable_syntax.php)
to connect _signals_ and _slots_, even if it is technically possible.
Refer to the [Advanced]({% link 03_advanced.md %}) section for more details. 


{: .good_to_know }
> * The number of parameters expected by a slot may be less than the number of parameters provided by the signal.
>   Any extra parameters are ignored, similar to regular PHP functions.
> * A signal can be linked to multiple slots, but the order in which they are called is not guaranteed.
> * A slot can be connected to multiple signals.

## Chaining signals
It is also possible to _chain_ signals together, using the `Siglot::chain<N>` methods,
where `<N>` represents the number of parameters of the input _signal_.
When a _signal_ is triggered, chained _signals_ will also be triggered with the same parameters.

```php
Siglot::chain0($button->clicked(...), $component->onSaveButtonClicked(...));
Siglot::chain1($text->changed(...), $component->onTextChanged(...));
```

{: .good_to_know }
> * It is possible for a destination signal to expect fewer parameters than the input signal provides.
    Any extra parameters are ignored, similar to regular PHP functions.
> * A signal can be chained to multiple signals, but the order in which they are called is not guaranteed.
> * Chaining is compatible with regular slot connections.


## Visibility
Signals and slots are regular PHP methods that you can define with any visibility.
However, the visibility affects the scope from which signals and slots can be connected.
For example, a class can connect a `public` _signal_ to one of its `private` _slots_,
or it can connect one of its `private` _signals_ to a `public` _slot_ of another class.
This is because accessibility is determined when the
[first-class callable syntax](https://www.php.net/manual/en/functions.first_class_callable_syntax.php)
is used, not at execution.

```php
class MyReceiver {
    public function __construct(MyEmitter $emitter) {
        Siglot::connect0($emitter->signal(...), $this->myPrivateSlot(...));
    }

    private function myPrivateSlot(): void {
        // ...
    }
}
```
 
{: .warning }
If you are not using [first-class callable syntax](https://www.php.net/manual/en/functions.first_class_callable_syntax.php),
scope resolution may be performed in Siglot's code, which will most likely result in failure.



## Connection lifetime
Once an emitter or a receiver is destroyed, all connections involving it are automatically removed.
It’s important to note that Siglot does not retain any references to connected objects in order to avoid interfering with PHP’s garbage collector.
This means you cannot depend on the existence of a connection to keep an object alive.

```php
function attachSignalLogger(MyEmitter $emitter): void {
    $logger = new class() {
        public function log(): void {
            echo "Signal received!\n";
        }
    };
    
    Siglot::connect0($emitter->signal(...), $logger->log(...));
    // ⚠️ $logger is destroyed here !!!
    // Nothing will be printed when the signal is emitted outside of this function.
}
```
