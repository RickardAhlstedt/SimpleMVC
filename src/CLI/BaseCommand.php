<?php

declare(strict_types=1);

namespace SimpleMVC\CLI;

use SimpleMVC\Core\Container;

abstract class BaseCommand
{
    public function __construct(
        protected Container $container
    ) {}

    /**
     * Execute the command.
     * @param array $args Arguments passed to the command (argv)
     * @return int Exit code
     */
    abstract public function execute(array $args = []): int;

    /**
     * Get help text for the command.
     */
    public function getHelp(): string
    {
        return 'No help available for this command.';
    }

    /**
     * Get a named flag (e.g. --force) from $args.
     */
    protected function getFlag(string $flag, array $args): bool
    {
        return in_array('--' . $flag, $args, true);
    }

    /**
     * Get a named parameter (e.g. --name value) from $args.
     */
    protected function getParam(string $param, array $args, $default = null)
    {
        $key = array_search('--' . $param, $args, true);
        if ($key !== false && isset($args[$key + 1]) && strpos($args[$key + 1], '--') !== 0) {
            return $args[$key + 1];
        }
        return $default;
    }

    /**
     * Get positional argument by index (skipping script name and command name).
     */
    protected function getArgument(int $index, array $args, $default = null)
    {
        // $args[0] = script, $args[1] = command name
        return $args[$index + 2] ?? $default;
    }
}
