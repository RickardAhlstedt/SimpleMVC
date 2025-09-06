<?php

declare(strict_types=1);

namespace SimpleMVC\Core;

class Container
{
    private static ?Container $instance = null;
    private array $definitions = [];
    private array $instances = [];

    public static function setInstance(Container $container): void
    {
        self::$instance = $container;
    }

    public static function getInstance(): ?Container
    {
        return self::$instance;
    }

    public function set(string $id, callable $factory): void
    {
        $this->definitions[$id] = $factory;
    }

    public function get(string $id)
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }
        if (!isset($this->definitions[$id])) {
            throw new \RuntimeException("Service '$id' not found.");
        }
        $this->instances[$id] = ($this->definitions[$id])($this);
        return $this->instances[$id];
    }

    public function has(string $id): bool
    {
        return isset($this->definitions[$id]);
    }
}
