<?php

declare(strict_types=1);

namespace SimpleMVC\Core;

use SimpleMVC\Compiler\Compiler;
use SimpleMVC\Compiler\ConfigCompilerPass;
use SimpleMVC\Compiler\ContainerCompilerPass;
use SimpleMVC\Compiler\RouteCompilerPass;
use SimpleMVC\Routing\Router;
use SimpleMVC\Event\Event;
use SimpleMVC\Event\EventDispatcher;

class Application
{
    private string $configDir;
    private string $cacheDir;

    public function __construct(string $configDir = __DIR__ . '/../../config', string $cacheDir = __DIR__ . '/../../cache')
    {
        $this->configDir = $configDir;
        $this->cacheDir = $cacheDir;
    }

    private function compileIfNeeded(): void
    {
        $compiledConfigFile = $this->cacheDir . '/config.php';
        $compiledContainerFile = $this->cacheDir . '/container.php';
        $compiledRoutesFile = $this->cacheDir . '/routes.php';

        if (!file_exists($compiledConfigFile)) {
            $compiler = new Compiler();
            $compiler->addPass(new ConfigCompilerPass($this->configDir));
            $compiler->compile($this->cacheDir);
        }

        if (!file_exists($compiledContainerFile)) {
            $compiler = new Compiler();
            $compiler->addPass(new ContainerCompilerPass($this->configDir));
            $compiler->compile($this->cacheDir);
        }

        if (!file_exists($compiledRoutesFile)) {
            $compiler = new Compiler();
            $compiler->addPass(new RouteCompilerPass($this->configDir));
            $compiler->compile($this->cacheDir);
        }

    }

    private function routeApplication() {
        $compiledRoutes = file_exists($this->cacheDir . '/routes.php') ? require $this->cacheDir . '/routes.php' : [];
        $dbRoutes = []; // Load from DB if needed
        $discoveredRoutes = []; // Use RouteDiscovery::discover()

        $router = new Router($compiledRoutes, $dbRoutes, $discoveredRoutes);

        // Strip query string for matching
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        $route = $router->match($uri, $_SERVER['REQUEST_METHOD']);
        if ($route) {
            $middlewares = [];
            // 1. Check for 'middleware' key on route (either as option or direct)
            if (!empty($route['middleware'])) {
                $middlewares = is_array($route['middleware']) ? $route['middleware'] : [$route['middleware']];
            } elseif (!empty($route['options']['middleware'])) {
                $middlewares = is_array($route['options']['middleware']) ? $route['options']['middleware'] : [$route['options']['middleware']];
            }

            $this->dispatch('application.route_matched', ['route' => $route]);

            foreach ($middlewares as $middlewareClass) {
                $this->dispatch('application.middleware_start', ['middleware' => $middlewareClass, 'route' => $route]);
                /** @var \SimpleMVC\Middleware\MiddlewareInterface $middleware */
                $middleware = \SimpleMVC\Core\Container::getInstance()->get($middlewareClass);
                if (!$middleware->handle($route)) {
                    $this->dispatch('application.middleware_failed', ['middleware' => $middlewareClass, 'route' => $route]);
                    // Middleware failed, stop execution
                    http_response_code(403);
                    echo "Forbidden";
                    return;
                }
                $this->dispatch('application.middleware_end', ['middleware' => $middlewareClass, 'route' => $route]);
            }

            $controller = \SimpleMVC\Core\Container::getInstance()->get($route['controller']);
            $action = $route['action'];

            $reflection = new \ReflectionMethod($controller, $action);
            $parameters = [];
            foreach ($reflection->getParameters() as $param) {
                $type = $param->getType();
                if ($type && !$type->isBuiltin()) {
                    $service = \SimpleMVC\Core\Container::getInstance()->get($type->getName());
                    // If it's a RequestStack, re-populate with current data
                    if ($service instanceof \SimpleMVC\Core\RequestStack) {
                        $service->populateFromGlobals();
                    }
                    $parameters[] = $service;
                } else {
                    $parameters[] = null; // Or handle default values
                }
            }
            $this->dispatch('application.controller_invoke', ['controller' => $route['controller'], 'action' => $action, 'parameters' => $parameters]);
            $response = $reflection->invokeArgs($controller, $parameters);
            $this->dispatch('application.controller_invoked', ['controller' => $route['controller'], 'action' => $action, 'response' => $response]);
            if ($response instanceof \SimpleMVC\Core\HTTP\Response) {
                $response->send();
            } else {
                echo $response; // Assume it's a string or something echoable
            }
        } else {
            http_response_code(404);
            echo "Not found";
        }
    }

    private function buildContainer(): void
    {
        $container = new Container();
        $serviceDefs = require $this->cacheDir . '/container.php';

        // Define available placeholders
        $vars = [
            '%PATH_ROOT%'     => defined('PATH_ROOT') ? PATH_ROOT : '',
            '%PATH_CORE%'     => defined('PATH_CORE') ? PATH_CORE : '',
            '%PATH_APP%'      => defined('PATH_APP') ? PATH_APP : '',
            '%PATH_CONFIG%'   => defined('PATH_CONFIG') ? PATH_CONFIG : '',
            '%PATH_CACHE%'    => defined('PATH_CACHE') ? PATH_CACHE : '',
            '%PATH_TEMPLATE%' => defined('PATH_TEMPLATE') ? PATH_TEMPLATE : '',
            '%PATH_PUBLIC%'   => defined('PATH_PUBLIC') ? PATH_PUBLIC : '',
            '%PATH_VENDOR%'   => defined('PATH_VENDOR') ? PATH_VENDOR : '',
            '%PATH_LOG%'      => defined('PATH_LOG') ? PATH_LOG : '',
        ];

        foreach ($serviceDefs as $id => $definition) {
            $container->set($id, function($c) use ($id, $definition, $vars) {
                $args = [];
                foreach ($definition['arguments'] ?? [] as $arg) {
                    if (is_string($arg) && str_starts_with($arg, '@')) {
                        $args[] = $c->get(substr($arg, 1));
                    } elseif (is_string($arg) && isset($vars[$arg])) {
                        $args[] = $vars[$arg];
                    } else {
                        $args[] = $arg;
                    }
                }
                return new $id(...$args);
            });
        }

        $dispatcher = $container->get(\SimpleMVC\Event\EventDispatcher::class);
        foreach ($serviceDefs as $id => $_) {
            if (method_exists($id, 'getSubscribedEvents')) {
                $listener = $container->get($id);
                foreach ($id::getSubscribedEvents() as $eventName => $method) {
                    $dispatcher->addListener($eventName, [$listener, $method]);
                }
            }
        }

        Container::setInstance($container);
    }

    private function dispatch(string $name, array $data = []): void
    {
        $dispatcher = Container::getInstance()->get(EventDispatcher::class);
        $dispatcher->dispatch(new Event($name, $data));
    }

    public function run(): void
    {
        $this->compileIfNeeded();
        $this->buildContainer();
        $this->dispatch('application.start');
        $this->dispatch('application.before_route');
        $this->routeApplication();
        $this->dispatch('application.after_route');
        $this->dispatch('application.end');
    }
}