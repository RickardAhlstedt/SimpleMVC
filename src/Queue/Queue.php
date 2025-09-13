<?php

namespace SimpleMVC\Queue;

class Queue
{
    public static function dispatch(string $jobName, array $payload = [], int $delay = 0): self
    {
        $queue = Container::getInstance()?->get(\SimpleMVC\Queue\DatabaseQueueDriver::class);
        $queue->dispatch($jobName, $payload, $delay);
        return new self();
    }
}