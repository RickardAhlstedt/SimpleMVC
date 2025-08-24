<?php

declare(strict_types=1);

namespace SimpleMVC\Discovery;

use ReflectionClass;
use SimpleMVC\CLI\BaseCommand;
use SimpleMVC\Attribute\Command as CommandAttr;

class CommandDiscovery
{
    /**
     * @param string $commandDir Directory to scan for commands
     * @return array Returns an array of discovered commands: [ [ 'name' => ..., 'description' => ..., 'class' => ... ], ... ]
     */
    public static function discover(string $commandDir): array
    {
        $commands = [];

        // 1. Scan directory for commands
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($commandDir)
        );

        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $className = self::getClassFullNameFromFile($file->getPathname());
                if (!$className || !class_exists($className)) {
                    continue;
                }

                $reflection = new ReflectionClass($className);

                // Must extend BaseCommand
                if (!$reflection->isSubclassOf(BaseCommand::class)) {
                    continue;
                }

                // Must have #[Command] attribute
                $commandAttr = $reflection->getAttributes(CommandAttr::class);
                if (empty($commandAttr)) {
                    continue;
                }

                /** @var CommandAttr $attrInstance */
                $attrInstance = $commandAttr[0]->newInstance();

                $commands[] = [
                    'name' => $attrInstance->name,
                    'description' => $attrInstance->description,
                    'class' => $className,
                ];
            }
        }

        return $commands;
    }

    private static function getClassFullNameFromFile(string $file): ?string
    {
        $src = file_get_contents($file);
        if (!preg_match('/namespace\s+([^;]+);/', $src, $m)) {
            return null;
        }
        $namespace = trim($m[1]);
        if (!preg_match('/class\s+([^\s{]+)/', $src, $m)) {
            return null;
        }
        $class = trim($m[1]);
        return $namespace . '\\' . $class;
    }
}