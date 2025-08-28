<?php

declare(strict_types=1);

namespace SimpleMVC\Core;

use Symfony\Component\Yaml\Yaml;

class Config
{
    private array $config = [];
    private static ?Config $instance = null;

    private array $envVars = [];    

    public static function getInstance(): Config
    {
        if (self::$instance === null) {
            self::$instance = new self([PATH_CONFIG, PATH_VAR . '/config']);
        }
        return self::$instance;
    }

    public static function setInstance(Config $config): void
    {
        self::$instance = $config;
    }

    public function __construct(array $configDirs)
    {
        $this->envVars = \SimpleMVC\Support\EnvVars::getEnvVars();
        foreach ($configDirs as $dir) {
            $this->loadConfigFromDir($dir);
        }
    }

    public function loadConfigFromDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = glob($dir . '/*.yaml');
        foreach ($files as $file) {
            $configData = Yaml::parseFile($file);
            if (is_array($configData)) {
                $this->config = array_merge_recursive($this->config, \SimpleMVC\Compiler\Compiler::replacePlaceholders($configData, $this->envVars));
            }
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