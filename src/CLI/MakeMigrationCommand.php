<?php

namespace SimpleMVC\CLI;

use SimpleMVC\Attribute\Command;

#[Command('make:migration', 'Create a new migration file')]
class MakeMigrationCommand extends BaseCommand
{
    public function execute(array $args = []): int
    {
        $name = $this->getArgument(0, $args);
        if ($name === null || empty($args)) {
            echo "Usage: make:migration <Name>\n";
            return 1;
        }

        $timestamp = date('YmdHis');
        $className = "Version{$timestamp}_{$name}";
        $fileName = PATH_MIGRATIONS . "/{$className}.php";

        $template = <<<PHP
        <?php

        use SimpleMVC\Database\Migration\MigrationInterface;

        class {$className} implements MigrationInterface
        {
            public function up(\\PDO \$connection): void
            {
                // TODO: write migration logic
            }

            public function down(\\PDO \$connection): void
            {
                // TODO: rollback migration logic
            }
        }
        PHP;

        if (!is_dir(PATH_MIGRATIONS)) {
            if (!mkdir($concurrentDirectory = PATH_MIGRATIONS, 0777, true) && !is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        }

        file_put_contents($fileName, $template);

        echo "Created migration: {$fileName}\n";
        return 0;
    }
}
