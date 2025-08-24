<?php

declare(strict_types=1);

namespace SimpleMVC\Routing;

class Router
{
    private array $compiledRoutes = [];
    private array $yamlRoutes = [];
    private array $dbRoutes = [];
    private array $discoveredRoutes = [];

    public function __construct(
        array $compiledRoutes = [],
        array $dbRoutes = [],
        array $discoveredRoutes = []
    ) {
        $this->compiledRoutes = $compiledRoutes;
        $this->dbRoutes = $dbRoutes;
        $this->discoveredRoutes = $discoveredRoutes;
    }

    public function match(string $path, string $method = 'GET'): ?array
    {
        // 1. Compiled routes
        foreach ($this->compiledRoutes as $route) {
            if ($route['path'] === $path && strtoupper($route['method']) === strtoupper($method)) {
                return $route;
            }
        }
        // 2. Database routes
        foreach ($this->dbRoutes as $route) {
            if ($route['path'] === $path && strtoupper($route['method']) === strtoupper($method)) {
                return $route;
            }
        }
        // 3. Controller-searching (discovered routes)
        foreach ($this->discoveredRoutes as $route) {
            if ($route['path'] === $path && strtoupper($route['method']) === strtoupper($method)) {
                return $route;
            }
        }
        return null;
    }
}