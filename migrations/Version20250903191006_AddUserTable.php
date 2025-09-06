<?php

use SimpleMVC\Database\Migration\MigrationInterface;

class Version20250903191006_AddUserTable implements MigrationInterface
{
    public function up(\PDO $connection): void
    {
        $connection->exec("
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT NOT NULL UNIQUE,
                password TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            );
        ");
    }

    public function down(\PDO $connection): void
    {
        $connection->exec("DROP TABLE IF EXISTS users;");
    }
}
