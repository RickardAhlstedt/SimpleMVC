<?php

use SimpleMVC\Database\Migration\MigrationInterface;

class Version20250828195630_CreateOptionsTable implements MigrationInterface
{
    public function up(\PDO $connection): void
    {
        $connection->exec("
            CREATE TABLE options (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL UNIQUE,
                value TEXT
            );
        ");
    }

    public function down(\PDO $connection): void
    {
        $connection->exec("DROP TABLE IF EXISTS options;");
    }
}
