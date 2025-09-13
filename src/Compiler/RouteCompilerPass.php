<?php

namespace SimpleMVC\Compiler;

use Symfony\Component\Yaml\Yaml;

class RouteCompilerPass implements CompilerPassInterface
{
    private string $configDir;

    public function __construct(string $configDir)
    {
        $this->configDir = $configDir;
    }

    public function process(string $cacheDir): void
    {
        $routesFile = $this->configDir . '/routes.yaml';
        if (!file_exists($routesFile)) {
            return;
        }

        $yaml = Yaml::parseFile($routesFile);
        $routes = $yaml['routes'] ?? [];

        // Sanity check
        foreach ($routes as $route) {
            // Missing required keys
            if (!isset($route['name'], $route['path'], $route['method'], $route['controller'], $route['action'])) {
                throw new \InvalidArgumentException('Invalid route definition in ' . $routesFile);
            }
            // Check if controller exists
            if (!class_exists($route['controller'])) {
                throw new \InvalidArgumentException('Controller class ' . $route['controller'] . ' does not exist in ' . $routesFile);
            }
            // Check if action exists
            if (!method_exists($route['controller'], $route['action'])) {
                throw new \InvalidArgumentException('Action ' . $route['action'] . ' does not exist in controller ' . $route['controller'] . ' in ' . $routesFile);
            }
        }

        // Run RouteDiscovery to find controllers and actions
        $routesDiscovery = \SimpleMVC\Discovery\RouteDiscovery::discover($this->configDir . '/../app/Controller');

        foreach ($routesDiscovery as $route) {
            $routes[] = [
                'name' => $route['name'],
                'path' => $route['path'],
                'method' => $route['method'],
                'controller' => $route['controller'],
                'action' => $route['action'],
                'middleware' => $route['middleware'] ?? null,
                'requirements' => $route['requirements'] ?? [],
                'converters' => $route['converters'] ?? []
            ];
        }

        $output = '<?php return ' . var_export($routes, true) . ';';
        file_put_contents($cacheDir . '/routes.php', $output);
    }

}
