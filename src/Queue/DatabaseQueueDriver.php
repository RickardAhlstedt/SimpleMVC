<?php

namespace SimpleMVC\Queue;

use JsonException;
use SimpleMVC\Database\Driver\DatabaseInterface;

class DatabaseQueueDriver implements QueueInterface
{

    public function __construct(
        private DatabaseInterface $database
    )
    {}

    /**
     * @throws JsonException
     */
    public function dispatch(string $name, array $payload = [], int $delay = 0): bool
    {
        $availableAt = time() + $delay;
        $stmt = $this->database->prepare("
            INSERT INTO jobs (name, payload, available_at, created_at)
            VALUES (:name, :payload, :available_at, :created_at)
        ");
        return $stmt->execute([
            'name' => $name,
            'payload' => json_encode($payload, JSON_THROW_ON_ERROR),
            'available_at' => $availableAt,
            'created_at' => time()
        ]);
    }

    public function reserve(): ?array
    {
        $this->database->beginTransaction();

        $stmt = $this->database->prepare("
        SELECT * FROM jobs
            WHERE available_at <= :now
            ORDER BY id ASC
            LIMIT 1
        ");
        $stmt->execute(['now' => time()]);
        $job = $stmt->fetch();

        if (!$job) {
            $this->database->commit();
            return null;
        }

        $this->delete($job['id']);
        $this->database->commit();

        return [
            'id' => $job['id'],
            'name' => $job['name'],
            'payload' => $job['payload'],
        ];
    }

    public function delete(int $id): bool
    {
        return $this->database->prepare("DELETE FROM jobs WHERE id = :id")->execute(['id' => $id]);
    }

    public function release(int $id, int $delay = 0): bool
    {
        $availableAt = time() + $delay;
        $stmt = $this->database->prepare("UPDATE jobs SET available_at = :available_at WHERE id = :id");
        return $stmt->execute(['available_at' => $availableAt, 'id' => $id]);
    }
}
