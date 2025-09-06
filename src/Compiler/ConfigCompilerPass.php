<?php

declare(strict_types=1);

namespace SimpleMVC\Compiler;

use Symfony\Component\Yaml\Yaml;

class ConfigCompilerPass implements CompilerPassInterface
{
    private string $configDir;

    public function __construct(string $configDir)
    {
        $this->configDir = $configDir;
    }

    public function process(string $cacheDir): void
    {
        $compiledConfig = [];
        $coreConfigDir = PATH_VAR_CONFIG;

        // Gather environment variables for replacement
        $envVars = \SimpleMVC\Support\EnvVars::getEnvVars();

        foreach (glob($coreConfigDir . '/*.yaml') as $file) {
            if ($file === $coreConfigDir . '/routes.yaml') {
                continue; // Skip routes file, handled separately
            }
            if ($file === $coreConfigDir . '/services.yaml') {
                continue; // Skip services file, handled separately
            }
            $name = basename($file, '.yaml');
            $parsed = Yaml::parseFile($file);

            // Recursively replace placeholders in config values
            $compiledConfig[$name] = \SimpleMVC\Compiler\Compiler::replacePlaceholders($parsed, $envVars);
        }

        foreach (glob($this->configDir . '/*.yaml') as $file) {
            if ($file === $this->configDir . '/routes.yaml') {
                continue; // Skip routes file, handled separately
            }
            if ($file === $this->configDir . '/services.yaml') {
                continue; // Skip services file, handled separately
            }
            $name = basename($file, '.yaml');
            $parsed = Yaml::parseFile($file);

            // Recursively replace placeholders in config values
            if (isset($compiledConfig[$name]) && is_array($compiledConfig[$name])) {
                $compiledConfig[$name] = array_replace_recursive($compiledConfig[$name], \SimpleMVC\Compiler\Compiler::replacePlaceholders($parsed, $envVars));
            } else {
                $compiledConfig[$name] = \SimpleMVC\Compiler\Compiler::replacePlaceholders($parsed, $envVars);
            }

        }

        $output = '<?php return ' . var_export($compiledConfig, true) . ';';

        file_put_contents($cacheDir . '/config.php', $output);
    }

}
