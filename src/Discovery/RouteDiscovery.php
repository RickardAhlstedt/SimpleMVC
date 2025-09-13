<?php

declare(strict_types=1);

namespace SimpleMVC\Discovery;

use ReflectionClass;
use ReflectionMethod;
use SimpleMVC\Attribute\Controller as ControllerAttr;
use SimpleMVC\Attribute\Route as RouteAttr;

class RouteDiscovery
{
    /**
     * @param string $controllerDir Directory to scan for controllers
     * @return array Returns an array of routes: [ [ 'path' => ..., 'method' => ..., 'controller' => ..., 'action' => ..., 'options' => ... ], ... ]
     */
    public static function discover(string $controllerDir): array
    {
        $routes = [];

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($controllerDir)
        );

        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $className = self::getClassFullNameFromFile($file->getPathname());
                if (!$className || !class_exists($className)) {
                    continue;
                }

                $reflection = new ReflectionClass($className);

                // Only scan classes with #[Controller]
                $controllerAttr = $reflection->getAttributes(ControllerAttr::class);
                if (empty($controllerAttr)) {
                    continue;
                }

                foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                    $routeAttrs = $method->getAttributes(RouteAttr::class);
                    foreach ($routeAttrs as $attr) {
                        /** @var RouteAttr $route */
                        $route = $attr->newInstance();
                        $routes[] = [
                            'name' => $route->name,
                            'path' => $route->path,
                            'method' => $route->method,
                            'controller' => $className,
                            'action' => $method->getName(),
                            'options' => $route->options,
                            'middleware' => $route->middleware,
                            'requirements' => $route->requirements,
                            'converters' => $route->converters,
                        ];
                    }
                }
            }
        }

        return $routes;
    }

    /**
     * Extracts the fully qualified class name from a PHP file.
     */
    private static function getClassFullNameFromFile(string $file): ?string
    {
        $src = file_get_contents($file);
        if (!preg_match('/namespace\s+([^;]+);/', $src, $m)) {
            return null;
        }
        $namespace = trim($m[1]);
        if (!preg_match('/class\s+([^\s{]+)/', $src, $m)) {
            return null;
        }
        $class = trim($m[1]);
        return $namespace . '\\' . $class;
    }
}
