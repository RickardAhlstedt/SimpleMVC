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
        // Helper closure to convert route path with placeholders to regex
        $compilePathToRegex = function ($route) {
            $pattern = preg_replace_callback(
                '/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/',
                static function ($matches) use ($route) {
                    $param = $matches[1];
                    if (isset($route['requirements'][$param])) {
                        $regex = $route['requirements'][$param];
                    } else {
                        $regex = '[^/]+';
                    }
                    return '(?P<' . $param . '>' . $regex . ')';
                },
                $route['path']
            );
            return '#^' . $pattern . '$#';
        };

        // 1. Compiled routes
        foreach ($this->compiledRoutes as $route) {
            $regex = $compilePathToRegex($route);
            if (preg_match($regex, $path, $matches) && strtoupper($route['method']) === strtoupper($method)) {
                $params = [];
                foreach ($matches as $key => $value) {
                    if (!is_int($key)) {
                        $params[$key] = $value;
                    }
                }
                $route['parameters'] = $params;
                return $route;
            }
        }
        // 2. Database routes
        foreach ($this->dbRoutes as $route) {
            $regex = $compilePathToRegex($route);
            if (preg_match($regex, $path, $matches) && strtoupper($route['method']) === strtoupper($method)) {
                $params = [];
                foreach ($matches as $key => $value) {
                    if (!is_int($key)) {
                        $params[$key] = $value;
                    }
                }
                $route['parameters'] = $params;
                return $route;
            }
        }
        // 3. Controller-searching (discovered routes)
        foreach ($this->discoveredRoutes as $route) {
            $regex = $compilePathToRegex($route);
            if (preg_match($regex, $path, $matches) && strtoupper($route['method']) === strtoupper($method)) {
                $params = [];
                foreach ($matches as $key => $value) {
                    if (!is_int($key)) {
                        $params[$key] = $value;
                    }
                }
                $route['parameters'] = $params;
                return $route;
            }
        }
        return null;
    }
}
