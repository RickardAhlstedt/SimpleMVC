<?php

declare(strict_types=1);

namespace SimpleMVC\CLI;

use SimpleMVC\Attribute\Command;
use SimpleMVC\CLI\BaseCommand;
use SimpleMVC\Core\Application;

#[Command('cache:clear', 'Clears and warms up the application cache')]
class CacheClearCommand extends BaseCommand
{
    public function execute(array $args = []): int
    {
        $cacheDir = PATH_CACHE;

        $noWarmUp = $this->getFlag('no-warmup', $args);

        // 1. Empty cache directory (except .gitignore if present)
        $files = glob($cacheDir . '/*');
        foreach ($files as $file) {
            if (is_dir($file)) {
                // Recursively delete directory
                $this->deleteDirectory($file);
            }
            if (is_file($file) && (basename($file) !== '.gitignore' || basename($file) !== '.gitkeep')) {
                unlink($file);
            }
        }

        echo "Cache cleared.\n";

        if ($noWarmUp) {
            echo "Skipping cache warmup.\n";
            return 0;
        }

        // 2. Warm up cache by running compiler passes
        $app = new Application(
            PATH_CONFIG,
            $cacheDir
        );
        // Only compile, don't run the app
        $reflection = new \ReflectionClass($app);
        $method = $reflection->getMethod('compileIfNeeded');
        $method->setAccessible(true);
        $method->invoke($app);

        $method = $reflection->getMethod('buildContainer');
        $method->setAccessible(true);
        $method->invoke($app);

        // Deleting any files left in /var/debug
        $debugDir = PATH_VAR . '/debug';
        $this->deleteDirectory($debugDir);

        echo "Cache warmed up.\n";
        return 0;
    }

    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }

    public function getHelp(): string
    {
        return "Usage: php bin/console.php cache:warmup\nClears and regenerates the application cache.";
    }
}
