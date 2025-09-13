<?php

namespace SimpleMVC\Queue;

interface QueueInterface
{
    public function dispatch(string $name, array $payload = [], int $delay = 0): bool;

    public function reserve(): ?array;

    public function delete(int $id): bool;

    public function release(int $id, int $delay = 0): bool;

}
