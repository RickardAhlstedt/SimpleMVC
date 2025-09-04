<?php

namespace SimpleMVC\Cache;

interface CacheInterface
{
    /**
     * Retrieve a cached value.
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Store a value in the cache.
     */
    public function set(string $key, mixed $value, int $ttl = 0): bool;

    /**
     * Check if a key exists in the cache.
     */
    public function has(string $key): bool;

    /**
     * Delete a value from the cache.
     */
    public function delete(string $key): bool;

    /**
     * Clear the entire cache.
     */
    public function clear(): bool;
}
