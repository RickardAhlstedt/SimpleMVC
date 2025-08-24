<?php

declare(strict_types=1);

define('PATH_ROOT', __DIR__ .'/..');
define('PATH_CORE', PATH_ROOT . '/src');
define('PATH_APP', PATH_ROOT . '/app');
define('PATH_CONFIG', PATH_ROOT . '/config');
define('PATH_CACHE', PATH_ROOT . '/cache');
define('PATH_TEMPLATE', PATH_ROOT . '/templates');
define('PATH_PUBLIC', PATH_ROOT . '/public');
define('PATH_VENDOR', PATH_ROOT . '/vendor');
define('PATH_LOG', PATH_ROOT . '/logs/app.log');

require __DIR__ . '/../vendor/autoload.php';

use SimpleMVC\Discovery\CommandDiscovery;

// Load extra commands from config cache
$extraCommands = CommandDiscovery::discover(__DIR__ . '/../src/CLI');

// Scan for commands in app/Command and add extra commands from config
$commands = CommandDiscovery::discover(__DIR__ . '/../app/Command');

$commands = array_merge($commands, $extraCommands);

// Build a map: command name => class
$commandMap = [];
foreach ($commands as $cmd) {
    $commandMap[$cmd['name']] = $cmd;
}

// Parse argv
$args = $argv ?? $_SERVER['argv'] ?? [];
$script = array_shift($args);
$commandName = array_shift($args);

// Show help if no command given
if (!$commandName || !isset($commandMap[$commandName])) {
    echo "Available commands:\n";
    foreach ($commandMap as $cmd) {
        echo "  {$cmd['name']}\t{$cmd['description']}\n";
    }
    exit(1);
}

// Instantiate and run the command
$cmdClass = $commandMap[$commandName]['class'];
/** @var \SimpleMVC\CLI\BaseCommand $command */
$command = new $cmdClass();

if (in_array('--help', $args, true)) {
    echo $command->getHelp() . PHP_EOL;
    exit(0);
}

exit($command->execute($argv));