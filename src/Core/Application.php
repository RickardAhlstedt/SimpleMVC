<?php

declare(strict_types=1);

namespace SimpleMVC\Core;

use ReflectionException;
use SimpleMVC\Compiler\Compiler;
use SimpleMVC\Compiler\ConfigCompilerPass;
use SimpleMVC\Compiler\ContainerCompilerPass;
use SimpleMVC\Compiler\RouteCompilerPass;
use SimpleMVC\Routing\Router;
use SimpleMVC\Event\Event;
use SimpleMVC\Event\EventDispatcher;
use SimpleMVC\Middleware\MiddlewarePipeline;
use SimpleMVC\Core\HTTP\RequestStack;
use SimpleMVC\Core\HTTP\Response;

class Application
{
    private string $configDir;
    private string $cacheDir;

    private static ?Application $instance = null;

    private MiddlewarePipeline $middlewarePipeline;

    public function __construct(string $configDir = __DIR__ . '/../../config', string $cacheDir = __DIR__ . '/../../cache')
    {
        $this->configDir = $configDir;
        $this->cacheDir = $cacheDir;
        $this->middlewarePipeline = new MiddlewarePipeline();
    }

    public static function getInstance(): Application
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function addMiddleware(string $middlewareClass): void
    {
        $this->dispatch('application.middleware_added', ['middleware' => $middlewareClass]);
        
        $container = Container::getInstance();
        $middleware = $container?->get($middlewareClass);
        $this->middlewarePipeline->addMiddleware($middleware);
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

    /**
     * @throws ReflectionException
     */
    private function routeApplication(): Response
    {
        $request = Container::getInstance()->get(RequestStack::class);
        $request->populateFromGlobals();

        // Create the final handler (controller execution)
        $finalHandler = function (RequestStack $request): Response {
            return $this->executeController($request);
        };

        // Process through middleware pipeline
        return $this->middlewarePipeline->handle($request, $finalHandler);
    }

    private function executeController(RequestStack $request): Response
    {
        $compiledRoutes = file_exists($this->cacheDir . '/routes.php') ? require $this->cacheDir . '/routes.php' : [];
        $dbRoutes = []; // Load from DB if needed
        $discoveredRoutes = []; // Use RouteDiscovery::discover()

        $router = new Router($compiledRoutes, $dbRoutes, $discoveredRoutes);

        // Strip query string for matching
        $uri = parse_url($request->getUri(), PHP_URL_PATH);

        $route = $router->match($uri, $request->getMethod());
        
        if (!$route) {
            return new Response("Not found", 404, ['Content-Type' => 'text/html; charset=utf-8']);
        }

        $this->dispatch('application.route_matched', ['route' => $route]);

        $controller = Container::getInstance()->get($route['controller']);
        $action = $route['action'];
        $resolverRegistry = Container::getInstance()->get(\SimpleMVC\Routing\RouteParamResolverRegistry::class);

        $reflection = new \ReflectionMethod($controller, $action);
        $parameters = [];
        
        foreach ($reflection->getParameters() as $param) {
            $paramName = $param->getName();
            $type = $param->getType();
            
            if (isset($route['parameters'][$paramName])) {
                $parameters[] = $resolverRegistry->resolve($paramName, $route['parameters'][$paramName], $param, Container::getInstance());
            } elseif ($type && !$type->isBuiltin()) {
                $service = Container::getInstance()->get($type->getName());
                $parameters[] = $service;
            } else {
                $parameters[] = null;
            }
        }

        $this->dispatch('application.controller_invoke', ['controller' => $route['controller'], 'action' => $action, 'parameters' => $parameters]);
        $response = $reflection->invokeArgs($controller, $parameters);
        $this->dispatch('application.controller_invoked', ['controller' => $route['controller'], 'action' => $action, 'response' => $response]);

        if ($response instanceof Response) {
            // Ensure HTML responses have proper Content-Type
            if (empty($response->getHeader('Content-Type')) && $this->isHtmlContent($response->getContent())) {
                $response->setHeader('Content-Type', 'text/html; charset=utf-8');
            }
            return $response;
        }

        // Convert string response to Response object with proper headers
        $content = (string)$response;
        $headers = [];
        if ($this->isHtmlContent($content)) {
            $headers['Content-Type'] = 'text/html; charset=utf-8';
        }

        return new Response($content, 200, $headers);
    }

    private function isHtmlContent(string $content): bool
    {
        return strpos($content, '<html') !== false || 
               strpos($content, '<!DOCTYPE') !== false || 
               strpos($content, '</body>') !== false;
    }

    private function buildContainer(): void
    {
        Bootstrap::buildContainer(PATH_CACHE);
    }

    private function dispatch(string $name, array $data = []): void
    {
        $dispatcher = Container::getInstance()->get(EventDispatcher::class);
        $dispatcher->dispatch(new Event($name, $data));
    }

    /**
     * @throws ReflectionException
     */
    public function run(): void
    {
        $this->compileIfNeeded();
        $this->buildContainer();

        if ($_ENV['APP_ENV'] === 'dev' || $_ENV['APP_ENV'] === 'test' || $_ENV['APP_ENV'] === 'local') {
            $debugToolbar = new \SimpleMVC\Debug\DebugToolbar(PATH_ROOT . '/var/debug');
            $debugToolbar->addCollector(new \SimpleMVC\Debug\Collector\RequestCollector());
            $debugToolbar->addCollector(new \SimpleMVC\Debug\Collector\DatabaseCollector());
            $debugToolbar->addCollector(new \SimpleMVC\Debug\Collector\PerformanceCollector());
            $debugToolbar->addCollector(new \SimpleMVC\Debug\Collector\DumpCollector());
            $debugToolbar->addCollector(new \SimpleMVC\Debug\Collector\TwigCollector());
            
            Container::getInstance()->set(\SimpleMVC\Debug\DebugToolbar::class, fn() => $debugToolbar);

            $this->addMiddleware(\SimpleMVC\Debug\Middleware\DebugToolbarMiddleware::class);
        }

        $this->dispatch('application.start');
        $this->dispatch('application.before_route');
        
        $response = $this->routeApplication();
        $response->send();
        
        $this->dispatch('application.after_route');
        $this->dispatch('application.end');
    }
}
