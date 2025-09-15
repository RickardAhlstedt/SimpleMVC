<?php

declare(strict_types=1);

namespace SimpleMVC\Templating;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Templating
{
    private Environment $twig;

    public function __construct(string $templateDir, string $cacheDir = null)
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

        // Register custom functions
        $functionClasses = glob(PATH_CORE . '/Twig/Functions/*.php');
        foreach ($functionClasses as $file) {
            $className = 'SimpleMVC\\Twig\\Functions\\' . basename($file, '.php');
            if (class_exists($className)) {
                $instance = new $className();
                if ($instance instanceof \SimpleMVC\Templating\Twig\TwigFunctionInterface) {
                    if (method_exists($instance, 'getFunctions')) {
                        foreach ($instance->getFunctions() as $function) {
                            $this->twig->addFunction($function);
                        }
                    } else {
                        $this->twig->addFunction($instance->getFunction());
                    }
                }
            }
        }
        // Register custom functions from app directory
        $functionClasses = glob(PATH_APP . '/Twig/Functions/*.php');
        foreach ($functionClasses as $file) {
            $className = 'SimpleMVC\\Twig\\Functions\\' . basename($file, '.php');
            if (class_exists($className)) {
                $instance = new $className();
                if ($instance instanceof \SimpleMVC\Templating\Twig\TwigFunctionInterface) {
                    if (method_exists($instance, 'getFunctions')) {
                        foreach ($instance->getFunctions() as $function) {
                            $this->twig->addFunction($function);
                        }
                    } else {
                        $this->twig->addFunction($instance->getFunction());
                    }
                }
            }
        }

        // Register custom filters
        $filterClasses = glob(PATH_CORE . '/Twig/Filters/*.php');
        foreach ($filterClasses as $file) {
            $className = 'SimpleMVC\\Twig\\Filters\\' . basename($file, '.php');
            if (class_exists($className)) {
                $instance = new $className();
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
        // Register custom filters from app directory
        $filterClasses = glob(PATH_APP . '/Twig/Filters/*.php');
        foreach ($filterClasses as $file) {
            $className = 'SimpleMVC\\Twig\\Filters\\' . basename($file, '.php');
            if (class_exists($className)) {
                $instance = new $className();
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

        // Register global variables
        $globalClasses = glob(PATH_CORE . '/Twig/Globals/*.php');
        foreach ($globalClasses as $file) {
            $className = 'SimpleMVC\\Twig\\Globals\\' . basename($file, '.php');
            if (class_exists($className)) {
                $instance = new $className();
                if ($instance instanceof \SimpleMVC\Templating\Twig\TwigGlobalInterface) {
                    $this->twig->addGlobal($instance->getName(), $instance->getValue());
                }
            }
        }
        // Register global variables from app directory
        $globalClasses = glob(PATH_APP . '/Twig/Globals/*.php');
        foreach ($globalClasses as $file) {
            $className = 'SimpleMVC\\Twig\\Globals\\' . basename($file, '.php');
            if (class_exists($className)) {
                $instance = new $className();
                if ($instance instanceof \SimpleMVC\Templating\Twig\TwigGlobalInterface) {
                    $this->twig->addGlobal($instance->getName(), $instance->getValue());
                }
            }
        }
        // Register CSRF token as a global
        $this->twig->addGlobal('csrf_token', \SimpleMVC\Security\CSRF::getToken());
    }

    public function render(string $template, array $params = []): string
    {
        return $this->twig->render($template, $params);
    }

    public function getTwig(): Environment
    {
        return $this->twig;
    }
}
