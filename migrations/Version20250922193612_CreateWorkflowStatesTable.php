<?php

use SimpleMVC\Database\Migration\MigrationInterface;

class Version20250922193612_CreateWorkflowStatesTable implements MigrationInterface
{
    public function up(\PDO $connection): void
    {
        $connection->exec(<<<SQL
        CREATE TABLE workflow_states (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            class VARCHAR(255) NOT NULL,
            class_id INTEGER NOT NULL,
            state VARCHAR(100) NOT NULL,
            workflow_name VARCHAR(100) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            UNIQUE(class, class_id, workflow_name)
        );
        SQL);

        $connection->exec(<<<SQL
        CREATE INDEX idx_workflow_states_class_id ON workflow_states(class, class_id);
        SQL);
    }

    public function down(\PDO $connection): void
    {
        $connection->exec(<<<SQL
        DROP INDEX IF EXISTS idx_workflow_states_class_id;
        SQL);

        $connection->exec(<<<SQL
        DROP TABLE IF EXISTS workflow_states;
        SQL);
    }
}