<?php

declare(strict_types=1);

namespace SimpleMVC\Core;

use Symfony\Component\Yaml\Yaml;

class Config
{
    private array $config = [];
    private static ?Config $instance = null;

    public static function getInstance(): Config
    {
        if (self::$instance === null) {
            self::$instance = new self(PATH_CONFIG);
        }
        return self::$instance;
    }

    public static function setInstance(Config $config): void
    {
        self::$instance = $config;
    }

    public function __construct(string $configDir)
    {
        // Load all YAML files in the config directory
        foreach (glob($configDir . '/*.yaml') as $file) {
            $name = basename($file, '.yaml');
            $this->config[$name] = Yaml::parseFile($file);
        }
    }

    /**
     * Get a config value by key, e.g. get('database.host')
     */
    public function get(string $key, $default = null)
    {
        $segments = explode('.', $key);
        $value = $this->config;

        foreach ($segments as $segment) {
            if (is_array($value) && array_key_exists($segment, $value)) {
                $value = $value[$segment];
            } else {
                return $default;
            }
        }

        return $value;
    }

    /**
     * Get the entire config array
     */
    public function all(): array
    {
        return $this->config;
    }
}