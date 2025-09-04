<?php

namespace SimpleMVC\CLI;

use ReflectionException;
use SimpleMVC\Attribute\Command;
use SimpleMVC\Database\Migration\Migrator;

#[Command('migrate:rollback', 'Rollback the latest applied migration')]
class RollbackDatabaseCommand extends BaseCommand
{

    /**
     * @throws ReflectionException
     */
    public function execute(array $args = []): int
    {
        $driver = $this->container->get(\SimpleMVC\Database\Driver\DatabaseInterface::class);
        $migrator = new Migrator($driver, PATH_MIGRATIONS);

        $rollback = $migrator->rollback();
        if ($rollback) {
            echo "Rolled back: {$rollback}\n";
        } else {
            echo "No migration to rollback.\n";
        }
        return 0;
    }
}
