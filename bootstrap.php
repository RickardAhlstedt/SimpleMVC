<?php
declare(strict_types=1);

define('PATH_ROOT', __DIR__);
define('PATH_CORE', PATH_ROOT . '/src');
define('PATH_APP', PATH_ROOT . '/app');
define('PATH_CONFIG', PATH_ROOT . '/config');
define('PATH_CACHE', PATH_ROOT . '/cache');
define('PATH_TEMPLATE', PATH_ROOT . '/templates');
define('PATH_PUBLIC', PATH_ROOT . '/public');
define('PATH_VENDOR', PATH_ROOT . '/vendor');
define('PATH_LOG', PATH_ROOT . '/logs/app.log');
define('PATH_VAR', PATH_ROOT . '/var');
define('PATH_VAR_CONFIG', PATH_VAR . '/config');
define('PATH_MIGRATIONS', PATH_ROOT . '/migrations');

require_once __DIR__ . '/vendor/autoload.php';

use SimpleMVC\Core\Application;

\SimpleMVC\Core\Env::load();

if ($_ENV['APP_ENV'] === 'dev' || $_ENV['APP_ENV'] === 'test' || $_ENV['APP_ENV'] === 'local') {
    if ($_ENV['APP_PRETTY_EXCEPTIONS'] ?? false) {
        $whoops = new \Whoops\Run;
        $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
        $whoops->register();
    }
}

$app = new Application(PATH_CONFIG, PATH_CACHE);
$app->run();
