<?php

declare(strict_types=1);

namespace SimpleMVC\Templating;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use SimpleMVC\Core\Container;

class Templating
{
    private Environment $twig;
    private Container $container;

    public function __construct(string $templateDir, string $cacheDir = null, Container $container = null)
    {
        $loader = new FilesystemLoader($templateDir);
        $options = [];
        if ($cacheDir) {
            $options['cache'] = $cacheDir;
        }

        $this->twig = new Environment($loader, $options);
        $this->registerTwigExtensions();
    }

    private function registerTwigExtensions(): void
    {
        // Add Twig profiling extension for debugging
        if ($this->isDebugMode()) {
            $this->twig->addExtension(new \SimpleMVC\Debug\Twig\ProfilingExtension());
        }

        // Register custom functions - store instances to maintain context
        $this->registerFunctionExtensions(PATH_CORE . '/Twig/Functions/*.php');
        $this->registerFunctionExtensions(PATH_APP . '/Twig/Functions/*.php');

        // Register custom filters
        $this->registerFilterExtensions(PATH_CORE . '/Twig/Filters/*.php');
        $this->registerFilterExtensions(PATH_APP . '/Twig/Filters/*.php');

        // Register global variables
        $this->registerGlobalExtensions(PATH_CORE . '/Twig/Globals/*.php');
        $this->registerGlobalExtensions(PATH_APP . '/Twig/Globals/*.php');

        // Register CSRF token as a global
        $this->twig->addGlobal('csrf_token', \SimpleMVC\Security\CSRF::getToken());
        
        // Get the requeststack and as a global
        $container = \SimpleMVC\Core\Container::getInstance();
        $request = $container?->get(\SimpleMVC\Core\HTTP\RequestStack::class);
        if (!$request) {
            $request = new \SimpleMVC\Core\HTTP\RequestStack();
        }
        $this->twig->addGlobal('request', $request);
        
        // Add session as a global
        $this->twig->addGlobal('session', \SimpleMVC\Core\HTTP\Session::all());
    }

    private function registerFunctionExtensions(string $pattern): void
    {
        $functionClasses = glob($pattern);
        foreach ($functionClasses as $file) {
            $className = 'SimpleMVC\\Twig\\Functions\\' . basename($file, '.php');
            if (class_exists($className)) {
                $tempInstance = new $className();
                
                if ($tempInstance instanceof \SimpleMVC\Templating\Twig\TwigFunctionInterface) {
                    foreach ($tempInstance->getFunctions() as $function) {
                        $this->twig->addFunction($function);
                    }
                }
            }
        }
    }

    private function registerFilterExtensions(string $pattern): void
    {
        $filterClasses = glob($pattern);
        foreach ($filterClasses as $file) {
            $className = 'SimpleMVC\\Twig\\Filters\\' . basename($file, '.php');
            if (class_exists($className)) {
                static $instances = [];
                if (!isset($instances[$className])) {
                    $instances[$className] = new $className();
                }
                
                $instance = $instances[$className];
                
                if ($instance instanceof \SimpleMVC\Templating\Twig\TwigFilterInterface) {
                    if (method_exists($instance, 'getFilters')) {
                        foreach ($instance->getFilters() as $filter) {
                            $this->twig->addFilter($filter);
                        }
                    } else {
                        $this->twig->addFilter($instance->getFilter());
                    }
                }
            }
        }
    }

    private function registerGlobalExtensions(string $pattern): void
    {
        $globalClasses = glob($pattern);
        foreach ($globalClasses as $file) {
            $className = 'SimpleMVC\\Twig\\Globals\\' . basename($file, '.php');
            if (class_exists($className)) {
                $instance = new $className();
                if ($instance instanceof \SimpleMVC\Templating\Twig\TwigGlobalInterface) {
                    $this->twig->addGlobal($instance->getName(), $instance->getValue());
                }
            }
        }
    }

    public function render(string $template, array $params = []): string
    {
        // Track template rendering
        if ($this->isDebugMode()) {
            \SimpleMVC\Debug\Collector\TwigCollector::startRender($template, $params);
        }

        try {
            $result = $this->twig->render($template, $params);
            
            if ($this->isDebugMode()) {
                \SimpleMVC\Debug\Collector\TwigCollector::endRender($template);
            }
            
            return $result;
        } catch (\Throwable $e) {
            if ($this->isDebugMode()) {
                \SimpleMVC\Debug\Collector\TwigCollector::addError($template, $e);
            }
            
            throw $e;
        }
    }

    private function isDebugMode(): bool
    {
        return (getenv('APP_ENV') === 'dev') || (getenv('APP_DEBUG') === 'true');
    }

    public function getTwig(): Environment
    {
        return $this->twig;
    }
}
