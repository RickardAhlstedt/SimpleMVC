<?php

namespace SimpleMVC\CLI;

use SimpleMVC\Attribute\Command;

#[Command('make:model', 'Create a new entity/model, with optional controller and migration')]
class MakeModelCommand extends BaseCommand
{
    public function execute(array $args = []): int
    {
        $name = $this->getArgument(0, $args);
        if ($name === null || empty($args)) {
            echo "Usage: make:model <Name> [-c|--controller] [-m|--migration]\n";
            return 1;
        }

        $modelName = ucfirst($name);
        $tableName = strtolower($modelName) . 's';

        $createController = $this->getFlag('controller', $args) || $this->getFlag('c', $args);
        $createMigration  = $this->getFlag('migration', $args) || $this->getFlag('m', $args);

        // 1. Create Entity
        $entityDir = PATH_APP . '/Entities';
        if (!is_dir($entityDir)) {
            mkdir($entityDir, 0777, true);
        }
        $entityFile = "{$entityDir}/{$modelName}.php";

        $entityTemplate = <<<PHP
<?php

namespace App\Entities;

class {$modelName}
{
    public int \$id;
    public string \$title;
    public string \$created_at;
    public string \$updated_at;
}
PHP;

        file_put_contents($entityFile, $entityTemplate);
        echo "Created entity: {$entityFile}\n";

        // 2. Optionally create Controller
        if ($createController) {
            $controllerDir = PATH_APP . '/Controller';
            if (!is_dir($controllerDir)) {
                mkdir($controllerDir, 0777, true);
            }
            $controllerFile = "{$controllerDir}/{$modelName}Controller.php";
            $controllerTemplate = <<<PHP
            <?php

            namespace App\Controller;

            use SimpleMVC\Attribute\Controller;
            use SimpleMVC\Attribute\Route;

            #[Controller]
            class {$modelName}Controller extends \\SimpleMVC\\Core\\HTTP\\AbstractController
            {
                #[Route(
                    name: "{$tableName}_index",
                    path: "/{$tableName}",
                    method: "GET"
                )]
                public function index()
                {
                    // TODO: implement index
                }
            }
            PHP;
            file_put_contents($controllerFile, $controllerTemplate);
            echo "Created controller: {$controllerFile}\n";
        }

        // 3. Optionally create Migration
        if ($createMigration) {
            $migrationDir = PATH_MIGRATIONS;
            if (!is_dir($migrationDir)) {
                mkdir($migrationDir, 0777, true);
            }
            $timestamp = date('YmdHis');
            $className = "Version{$timestamp}_Create{$modelName}";
            $migrationFile = "{$migrationDir}/{$className}.php";

            $baseTableSql = "CREATE TABLE {$tableName} (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title VARCHAR(255) NOT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL
            );";

            $migrationTemplate = <<<PHP
            <?php

            use SimpleMVC\Database\Migration\MigrationInterface;

            class {$className} implements MigrationInterface
            {
                public function up(\\PDO \$connection): void
                {
                    \$connection->exec(<<<SQL
                    {$baseTableSql}
                    SQL);
                }

                public function down(\\PDO \$connection): void
                {
                    \$connection->exec("DROP TABLE IF EXISTS {$tableName};");
                }
            }
            PHP;

            file_put_contents($migrationFile, $migrationTemplate);
            echo "Created migration: {$migrationFile}\n";
        }

        return 0;
    }
}
