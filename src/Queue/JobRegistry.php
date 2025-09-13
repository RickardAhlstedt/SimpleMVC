<?php

namespace SimpleMVC\Queue;

use SimpleMVC\Core\Container;

class JobRegistry
{
    private array $map = [];

    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function register(string $name, string $jobClass): void
    {
        $this->map[$name] = $jobClass;
    }

    public function resolve(string $name): ?JobInterface
    {
        if (!isset($this->map[$name])) {
            return null;
        }

        $class = $this->map[$name];
        return $this->container->get($class);
    }

}
