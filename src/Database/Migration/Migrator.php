<?php

declare(strict_types=1);

namespace SimpleMVC\Database\Migration;

use PDO;
use ReflectionClass;
use ReflectionException;
use SimpleMVC\Database\Driver\DatabaseInterface;

class Migrator
{
    private PDO $pdo;
    private string $path;

    /**
     * @throws ReflectionException
     */
    public function __construct(DatabaseInterface $driver, string $path)
    {
        // Drivers already wrap PDO, so let’s extract it
        $reflection = new ReflectionClass($driver);
        $prop = $reflection->getProperty('pdo');
        $prop->setAccessible(true);
        $this->pdo = $prop->getValue($driver);
        $this->path = $path;

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
            version TEXT PRIMARY KEY,
            applied_at TEXT NOT NULL
        )");
    }

    public function migrate(): void
    {
        $applied = $this->getAppliedVersions();

        foreach (glob($this->path . '/Version*.php') as $file) {
            require_once $file;
            $className = basename($file, '.php');

            if (in_array($className, $applied, true)) {
                continue;
            }

            $migration = new $className();
            if ($migration instanceof MigrationInterface) {
                $migration->up($this->pdo);
                $this->recordMigration($className);
                echo "Applied $className\n";
            }
        }
    }

    private function getAppliedVersions(): array
    {
        return $this->pdo->query("SELECT version FROM migrations")->fetchAll(PDO::FETCH_COLUMN);
    }

    private function recordMigration(string $version): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO migrations (version, applied_at) VALUES (:version, :at)");
        $stmt->execute([
            'version' => $version,
            'at' => date('c'),
        ]);
    }

    public function rollback(): ?string
    {
        $stmt = $this->pdo->query("SELECT version FROM migrations ORDER BY applied_at DESC LIMIT 1");
        $version = $stmt->fetchColumn();
        $stmt->closeCursor();

        if (!$version) {
            return null;
        }

        require_once PATH_MIGRATIONS . "/{$version}.php";
        $migration = new $version();
        $migration->down($this->pdo);

        $this->pdo->prepare("DELETE FROM migrations WHERE version = :v")->execute(['v' => $version]);

        return $version;
    }

    public function reset(): void
    {
        // Rollback all applied migrations in reverse order
        $stmt = $this->pdo->query("SELECT version FROM migrations ORDER BY applied_at DESC");
        $versions = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        $stmt->closeCursor();

        foreach ($versions as $version) {
            require_once PATH_MIGRATIONS . "/{$version}.php";
            $migration = new $version();
            $migration->down($this->pdo);

            $this->pdo->prepare("DELETE FROM migrations WHERE version = :v")->execute(['v' => $version]);
            echo "Rolled back: {$version}\n";
        }
    }

}
