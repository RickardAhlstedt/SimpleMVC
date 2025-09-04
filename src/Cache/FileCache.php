<?php

namespace SimpleMVC\Cache;

class FileCache implements CacheInterface
{
    private string $path;

    public function __construct(string $path)
    {
        $this->path = rtrim($path, '/');
        if (!is_dir($this->path)) {
            if (!mkdir($concurrentDirectory = $this->path, 0777, true) && !is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        }
    }

    private function getFileName(string $key): string
    {
        return $this->path . '/' . md5($key) . '.cache';
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $file = $this->getFileName($key);
        if (!file_exists($file)) {
            return $default;
        }

        $data = unserialize(file_get_contents($file));
        if ($data['ttl'] !== 0 && $data['ttl'] < time()) {
            unlink($file);
            return $default;
        }
        return $data['value'];
    }

    public function set(string $key, mixed $value, int $ttl = 0): bool
    {
        $file = $this->getFileName($key);
        $data = [
            'value' => $value,
            'ttl' => $ttl > 0 ? time() + $ttl : 0
        ];
        return file_put_contents($file, serialize($data)) !== false;
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function delete(string $key): bool
    {
        $file = $this->getFileName($key);
        return file_exists($file) ? unlink($file) : true;
    }

    public function clear(): bool
    {
        foreach (glob($this->path . '/*.cache') as $file) {
            unlink($file);
        }
        return true;
    }
}
