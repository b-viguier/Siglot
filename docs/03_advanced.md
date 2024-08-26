---
title: Advanced
nav_order: 3
permalink: /advanced
---

# Advanced
{: .no_toc }

Here are some optional details if you're curious about how Siglot works.

* TOC
{:toc}

## Implementing [`Emitter`](https://github.com/b-viguier/Siglot/blob/main/src/Emitter.php) interface

Siglot stores all connections in an [`Internal\SignalManager`](https://github.com/b-viguier/Siglot/blob/main/src/Internal/SignalManager.php)
that is stored in the _emitter_ instance.
The goal of the [`Emitter`](https://github.com/b-viguier/Siglot/blob/main/src/Emitter.php)
interface is to expose signals of the [`Internal\SignalManager`](https://github.com/b-viguier/Siglot/blob/main/src/Internal/SignalManager.php)
without exposing a way to _emit_ them from outside the class.
This design ensures that signals are only emitted from the class that defines them and its subclasses.
The [`EmitterHelper`](https://github.com/b-viguier/Siglot/blob/main/src/EmitterHelper.php)
trait already includes everything needed to implement the [`Emitter`](https://github.com/b-viguier/Siglot/blob/main/src/Emitter.php) interface.
However, if more control is needed, one can refer to the trait's implementation to manually implement the interface.


{: .warning }
Classes in the `Internal` namespace are not meant to be used directly
and may be removed or changed without notice.

## Storage of connections

All connections must be stored in the _emitter_ instance in order to share its lifetime.
This is transparently achieved by the [`EmitterHelper`](https://github.com/b-viguier/Siglot/blob/main/src/EmitterHelper.php) trait,
but you may need to keep this in mind when dealing with some serialization functions for your _emitter_ class.

## Performance

In theory, there is a certain amount of overhead associated with calling a slot function from a signal.
This overhead depends on the number of signals in the object and the number of slots connected to the called signal.
In practice, this overhead should be less than 10 microseconds and is usually negligible.
You can see the [benchmark](https://github.com/b-viguier/Siglot/tree/main/examples/benchmark.php) example to try it yourself.

{: .good_to_know }
When a slot is called directly, there is no overhead even if it is connected to several signals.


## Connecting closures

In order to connect signals to slots, Siglot utilizes closure objects to access related instances and methods through reflection.
It is best to use [first class callable syntax](https://www.php.net/manual/en/functions.first_class_callable_syntax.php),
as it ensures that the method exists and is visible, resulting in more readable code.
Additionally, your favorite IDE will be able to offer autocompletion and static analysis.
While it is possible to use the `\Closure::fromCallable([$emitter, 'signal'])` syntax, it is discouraged due to being less readable.

{: .warning }
Providing a closure that does not match a signal or slot function will result in a runtime error. 
