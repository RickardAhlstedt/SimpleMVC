<?php

namespace SimpleMVC\Jobs;

use SimpleMVC\Queue\JobInterface;

class TestJob implements JobInterface
{
    public function handle(array|string $payload): void
    {
        var_dump($payload);
        return;
    }
}
