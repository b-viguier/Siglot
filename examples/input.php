<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Bviguier\Siglot;

/*======================================================================================================================
Class KbdInput
A class to listen to the keyboard input and emit a signal when a new command is entered.
======================================================================================================================*/
class KbdInput implements Siglot\Emitter
{
    use Siglot\EmitterHelper;

    public function listen(): void
    {
        if ($this->isListening) {
            throw new \Exception('Already listening');
        }

        $this->isListening = true;
        while ($this->isListening) {
            echo 'Enter a command: ';
            $line = fgets(\STDIN);
            if ($line === false) {
                break;
            }
            $this->emit($this->newCommandTriggered(trim($line)));
        }
    }

    public function stop(): void
    {
        $this->isListening = false;
    }

    public function newCommandTriggered(string $command): Siglot\SignalEvent
    {
        return Siglot\SignalEvent::auto();
    }

    private bool $isListening = false;
}

/*======================================================================================================================
Class MyApp
A class to handle the commands and emit a signal when the application has been stopped.
======================================================================================================================*/
class MyApp implements Siglot\Emitter
{
    use Siglot\EmitterHelper;

    public function execCommand(string $command): void
    {
        switch ($command) {
            case 'exit':
                $this->emit($this->stopped());
                break;
            case 'help':
                echo 'Available commands:' . \PHP_EOL;
                echo '  - exit: stop the application' . \PHP_EOL;
                echo '  - help: display this help' . \PHP_EOL;
                break;
            default:
                echo 'Unknown command' . \PHP_EOL;
        }
    }

    public function stopped(): Siglot\SignalEvent
    {
        return Siglot\SignalEvent::auto();
    }
}

/*======================================================================================================================
Main
======================================================================================================================*/
$app = new MyApp();
$input = new KbdInput();
Siglot\Siglot::connect1($input->newCommandTriggered(...), $app->execCommand(...));
Siglot\Siglot::connect0($app->stopped(...), $input->stop(...));

$input->listen();
