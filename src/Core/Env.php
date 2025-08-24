<?php

declare(strict_types=1);

namespace SimpleMVC\Core;

class Env
{
    /**
     * Loads environment variables from a .env file into $_ENV and getenv().
     * Existing variables are not overwritten.
     */
    public static function load(string $envFile = __DIR__ . '/../../.env'): void
    {
        if (!file_exists($envFile)) {
            return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (!preg_match('/^\s*([\w\.]+)\s*=\s*(.*)?\s*$/', $line, $matches)) {
                continue;
            }
            $name = $matches[1];
            $value = $matches[2];

            // Remove surrounding quotes
            if ($value && ($value[0] === '"' || $value[0] === "'")) {
                $value = substr($value, 1, -1);
            }

            if (getenv($name) === false && !isset($_ENV[$name])) {
                putenv("$name=$value");
                $_ENV[$name] = $value;
            }
        }
    }
}