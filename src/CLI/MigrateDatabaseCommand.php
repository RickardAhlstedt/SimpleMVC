<?php

namespace SimpleMVC\CLI;

use SimpleMVC\Attribute\Command;
use SimpleMVC\Database\Migration\Migrator;
use SimpleMVC\Core\Container;

#[Command('migrate', 'Migrate Database')]
class MigrateDatabaseCommand extends BaseCommand
{
    public function execute(array $args = []): int
    {
        $driver = $this->container->get(\SimpleMVC\Database\Driver\DatabaseInterface::class);
        $migrator = new Migrator($driver, PATH_MIGRATIONS);
        $migrator->migrate();
        return 0;
    }
}
