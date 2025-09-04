<?php

use SimpleMVC\Database\Migration\MigrationInterface;

class Version20250904080118_AddQueueTable implements MigrationInterface
{
    public function up(\PDO $connection): void
    {
        $connection->exec("
        CREATE TABLE jobs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            payload TEXT NOT NULL,
            available_at INTEGER NOT NULL,
            created_at INTEGER NOT NULL
        );
        ");
    }

    public function down(\PDO $connection): void
    {
        $connection->exec("DROP TABLE IF EXISTS jobs;");
    }
}
