<?php

namespace SimpleMVC\Cache;

use PDO;

class DatabaseCache implements CacheInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS cache (
                key TEXT PRIMARY KEY,
                value BLOB,
                expires INTEGER
            )
        ");
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $stmt = $this->pdo->prepare("SELECT value, expires FROM cache WHERE key = :key");
        $stmt->execute(['key' => $key]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return $default;
        }

        if ($row['expires'] != 0 && $row['expires'] < time()) {
            $this->delete($key);
            return $default;
        }

        return unserialize($row['value']);
    }

    public function set(string $key, mixed $value, int $ttl = 0): bool
    {
        $expires = $ttl > 0 ? time() + $ttl : 0;
        $stmt = $this->pdo->prepare("
            INSERT INTO cache (key, value, expires) VALUES (:key, :value, :expires)
            ON CONFLICT(key) DO UPDATE SET value = :value, expires = :expires
        ");
        return $stmt->execute([
            'key' => $key,
            'value' => serialize($value),
            'expires' => $expires,
        ]);
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function delete(string $key): bool
    {
        return $this->pdo->prepare("DELETE FROM cache WHERE key = :key")->execute(['key' => $key]);
    }

    public function clear(): bool
    {
        return $this->pdo->exec("DELETE FROM cache") !== false;
    }
}
