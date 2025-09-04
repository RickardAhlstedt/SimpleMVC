<?php

namespace SimpleMVC\Queue;

interface JobInterface
{

    public function handle(array $payload): void;

}
