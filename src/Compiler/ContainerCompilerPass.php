<?php

declare(strict_types=1);

namespace SimpleMVC\Compiler;

use Symfony\Component\Yaml\Yaml;

class ContainerCompilerPass implements CompilerPassInterface
{
    private string $configDir;

    public function __construct(string $configDir)
    {
        $this->configDir = $configDir;
    }

    public function process(string $cacheDir): void
    {
        $servicesFile = $this->configDir . '/services.yaml';
        if (!file_exists($servicesFile)) {
            return;
        }

        // Gather environment variables for replacement
        $envVars = \SimpleMVC\Support\EnvVars::getEnvVars();

        $yaml = Yaml::parseFile($servicesFile);
        $services = $yaml['services'] ?? [];

        $services = \SimpleMVC\Compiler\Compiler::replacePlaceholders($services, $envVars);

        $output = '<?php return ' . var_export($services, true) . ';';
        file_put_contents($cacheDir . '/container.php', $output);
    }
}