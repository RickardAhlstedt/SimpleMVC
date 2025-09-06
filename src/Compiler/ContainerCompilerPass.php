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
        $servicesFiles = [
            PATH_VAR_CONFIG . '/services.yaml',
            $this->configDir . '/services.yaml',
        ];

        $services = [];

        // Gather environment variables for replacement
        $envVars = \SimpleMVC\Support\EnvVars::getEnvVars();

        foreach ($servicesFiles as $servicesFile) {
            if (!file_exists($servicesFile)) {
                continue;
            }

            $yaml = Yaml::parseFile($servicesFile);
            $parsedServices = $yaml['services'] ?? [];

            $parsedServices = \SimpleMVC\Compiler\Compiler::replacePlaceholders($parsedServices, $envVars);

            $services = array_replace_recursive($services, $parsedServices);
        }

        $output = '<?php return ' . var_export($services, true) . ';';
        file_put_contents($cacheDir . '/container.php', $output);
    }
}
