---
title: Getting Started
nav_order: 0
permalink: /
---

# Getting Started
{: .no_toc }

* TOC
{:toc}

## What is _Siglot_
Signals and slots is a mechanism introduced in [Qt](https://doc.qt.io/qt-6/signalsandslots.html)
for communication between objects.
It makes it easy to implement the observer pattern while avoiding boilerplate code.
_Siglot_ aims to provide similar features for the PHP language, with particular attention to Developer Experience (DX):
* Easy to write, utilizing the [first-class callable syntax](https://www.php.net/manual/en/functions.first_class_callable_syntax.php)
* Compatible with existing auto-completion
* Compatible with static analysis tools
* No side effects with references

It can be regarded as an alternative to callbacks or event dispatchers, and it's particularly suited for
[event-driven programming](https://en.wikipedia.org/wiki/Event-driven_programming),
to decouple events' sender and receiver.

## Installation

To install Siglot, you can use [Composer](https://getcomposer.org/):

```bash
composer require b-viguier/siglot
```


## Example

Let's define a `$button` object, with a `$button->clicked()` _signal_ function that can be triggered with the `$button->click()` function. 
```php
$button = new class() implements Siglot\Emitter {
    use Siglot\EmitterHelper;

    // This is our signal
    public function clicked(): Siglot\SignalEvent
    {
        return Siglot\SignalEvent::auto();
    }
    
    // This function triggers the signal above
    public function click(): void
    {
        $this->emit($this->clicked());
    }
};
```

Now let's create a `$receiver` object, with a `$receiver->onClick()` method that will be used as a _slot_.
```php
$receiver = new class() {
    // This is our slot
    public function onClick(): void
    {
        echo "Button clicked!\n";
    }
};
```

We can now connect the `$button->clicked()` _signal_ to the `$receiver->onClick()` _slot_.
```php
Siglot\Siglot::connect0(
    $button->clicked(...),
    $receiver->onClick(...),
);
```

Now, each time the _signal_ is triggered with the `$button->click()` method, the connected slot will be called. 
```php
$button->click();
// Displays: Button clicked!
```


