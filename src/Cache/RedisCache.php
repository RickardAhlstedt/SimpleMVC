<?php

namespace SimpleMVC\Cache;

use Redis;

class RedisCache implements CacheInterface
{
    private Redis $redis;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->redis->get($key);
        return $value === false ? $default : unserialize($value);
    }

    public function set(string $key, mixed $value, int $ttl = 0): bool
    {
        $data = serialize($value);
        return $ttl > 0
            ? $this->redis->setex($key, $ttl, $data)
            : $this->redis->set($key, $data);
    }

    public function has(string $key): bool
    {
        return $this->redis->exists($key) > 0;
    }

    public function delete(string $key): bool
    {
        return $this->redis->del($key) > 0;
    }

    public function clear(): bool
    {
        return $this->redis->flushDB();
    }
}
