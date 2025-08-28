<?php

namespace SimpleMVC\Database\Driver\Sqlite;

use PDO;
use PDOException;

use SimpleMVC\Database\Driver\DatabaseInterface;

class SqliteDatabaseDriver implements DatabaseInterface
{
    private ?PDO $pdo = null;

    public function connect(array $config): void
    {
        $path = $config['path'] ?? ':memory:';
        $dsn = 'sqlite:' . $path;

        try {
            $this->pdo = new PDO($dsn);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new \RuntimeException('SQLite connection failed: ' . $e->getMessage(), 0, $e);
        }
    }

    public function query(string $sql, array $params = []): mixed
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function prepare(string $sql): mixed
    {
        return $this->pdo->prepare($sql);
    }

    public function execute(mixed $statement, array $params = []): bool
    {
        return $statement->execute($params);
    }

    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    public function rollback(): bool
    {
        return $this->pdo->rollBack();
    }

    public function lastInsertId(): string|false
    {
        return $this->pdo->lastInsertId();
    }

    public function close(): void
    {
        $this->pdo = null;
    }
}