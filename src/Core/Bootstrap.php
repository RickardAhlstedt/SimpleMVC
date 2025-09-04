<?php

namespace SimpleMVC\Core;

class Bootstrap
{
    public static function buildContainer(string $cacheDir): Container
    {
        $container = new Container();
        $serviceDefs = require $cacheDir . '/container.php';

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

        if (!empty(Config::getInstance()->get('database'))) {
            $dbConfig = Config::getInstance()->get('database');

            switch($dbConfig['driver']) {
                case 'sqlite':
                    if (isset($dbConfig['path']) && $dbConfig['path'] !== ':memory:') {
                        $dir = dirname($dbConfig['path']);
                        if (!is_dir($dir)) {
                            if (!mkdir($dir, 0777, true) && !is_dir($dir)) {
                                throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
                            }
                        }
                        if (!file_exists($dbConfig['path'])) {
                            touch($dbConfig['path']);
                        }
                    }
                    $driver = new \SimpleMVC\Database\Driver\Sqlite\SqliteDatabaseDriver();
                    $driver->connect($dbConfig);
                    $container->set(\SimpleMVC\Database\Driver\DatabaseInterface::class, fn() => $driver);
                    break;
                // Add other drivers here (e.g. MySQL, PostgreSQL)
                case 'pdo':
                    $driver = new \SimpleMVC\Database\Driver\Pdo\PdoDatabaseDriver();
                    $driver->connect($dbConfig);
                    $container->set(\SimpleMVC\Database\Driver\DatabaseInterface::class, fn() => $driver);
                    break;
                default:
                    throw new \RuntimeException('Unsupported database driver: ' . $dbConfig['driver']);

            }

            // Cache system wiring
            $cacheConfig = Config::getInstance()->get('cache');
            if (!empty($cacheConfig) && isset($cacheConfig['driver'])) {
                switch ($cacheConfig['driver']) {
                    case 'file':
                        $cachePath = $cacheConfig['path'] ?? '';
                        if ($cachePath && !is_dir($cachePath) && !mkdir($cachePath, 0777, true) && !is_dir($cachePath)) {
                            throw new \RuntimeException(sprintf('Cache directory "%s" was not created', $cachePath));
                        }
                        $cacheDriver = new \SimpleMVC\Cache\FileCache($cachePath);
                        $container->set(\SimpleMVC\Cache\CacheInterface::class, fn() => $cacheDriver);
                        break;
                    case 'database':
                        $dbDriver = $container->get(\SimpleMVC\Database\Driver\DatabaseInterface::class);
                        if (!method_exists($dbDriver, 'getConnection')) {
                            throw new \RuntimeException('Database driver does not provide a PDO connection for DatabaseCache');
                        }
                        $pdo = $dbDriver->getConnection();
                        $cacheDriver = new \SimpleMVC\Cache\DatabaseCache($pdo);
                        $container->set(\SimpleMVC\Cache\CacheInterface::class, fn() => $cacheDriver);
                        break;
                    case 'redis':
                        $host = $cacheConfig['host'] ?? '127.0.0.1';
                        $port = $cacheConfig['port'] ?? 6379;
                        $redis = new \Redis();
                        $redis->connect($host, $port);
                        $cacheDriver = new \SimpleMVC\Cache\RedisCache($redis);
                        $container->set(\SimpleMVC\Cache\CacheInterface::class, fn() => $cacheDriver);
                        break;
                    default:
                        throw new \RuntimeException('Unsupported cache driver: ' . $cacheConfig['driver']);
                }
            }
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
        return $container;
    }
}
