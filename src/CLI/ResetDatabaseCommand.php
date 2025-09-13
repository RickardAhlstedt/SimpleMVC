<?php

namespace SimpleMVC\CLI;

use ReflectionException;
use SimpleMVC\Attribute\Command;
use SimpleMVC\Database\Migration\Migrator;

#[Command('migrate:reset', 'Drop all the tables and rerun all migrations')]
class ResetDatabaseCommand extends BaseCommand
{
    /**
     * @throws ReflectionException
     */
    public function execute(array $args = []): int
    {
        $driver = $this->container->get(\SimpleMVC\Database\Driver\DatabaseInterface::class);
        $migrator = new Migrator($driver, PATH_MIGRATIONS);
        $migrator->reset();
        return 0;
    }
}
