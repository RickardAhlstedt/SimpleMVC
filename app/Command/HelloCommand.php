<?php

declare(strict_types=1);

namespace App\Command;

use SimpleMVC\Attribute\Command;
use SimpleMVC\CLI\BaseCommand;

#[Command('hello', 'Prints a hello message')]
class HelloCommand extends BaseCommand
{
    public function execute(array $args = []): int
    {
        $name = $this->getParam('name', $args, 'World');
        $shout = $this->getFlag('shout', $args);

        $message = "Hello, $name!";
        if ($shout) {
            $message = strtoupper($message);
        }

        echo $message . PHP_EOL;
        return 0;
    }

    public function getHelp(): string
    {
        return "Usage: php cli.php hello --name <name> [--shout]";
    }
}