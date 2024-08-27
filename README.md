# Siglot [![Siglot CI](https://github.com/b-viguier/Siglot/actions/workflows/ci.yml/badge.svg)](https://github.com/b-viguier/Siglot/actions/workflows/ci.yml)


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

**For more information, see the [documentation](https://b-viguier.github.io/Siglot).**


## Local Development

You can use dedicated Docker containers:
```bash
make build up bash-8.3
```

See `make help` for more information.
Once you are in a suitable environment, use `composer` scripts to run tests, etc. (see `composer list`).
