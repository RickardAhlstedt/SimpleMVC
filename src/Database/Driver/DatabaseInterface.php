<?php

namespace SimpleMVC\Database\Driver;

interface DatabaseInterface
{
    public function connect(array $config): void;

    public function query(string $sql, array $params = []): mixed;

    public function prepare(string $sql): mixed;

    public function execute(mixed $statement, array $params = []): bool;

    public function beginTransaction(): bool;

    public function commit(): bool;

    public function rollback(): bool;

    public function lastInsertId(): string|false;

    public function close(): void;
}