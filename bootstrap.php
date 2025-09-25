<?php
declare(strict_types=1);

const PATH_ROOT = __DIR__;
const PATH_CORE = PATH_ROOT . '/src';
const PATH_APP = PATH_ROOT . '/app';
const PATH_CONFIG = PATH_ROOT . '/config';
const PATH_CACHE = PATH_ROOT . '/cache';
const PATH_TEMPLATE = PATH_ROOT . '/templates';
const PATH_PUBLIC = PATH_ROOT . '/public';
const PATH_VENDOR = PATH_ROOT . '/vendor';
const PATH_LOG = PATH_ROOT . '/logs/app.log';
const PATH_VAR = PATH_ROOT . '/var';
const PATH_VAR_CONFIG = PATH_VAR . '/config';
const PATH_MIGRATIONS = PATH_ROOT . '/migrations';

require_once __DIR__ . '/vendor/autoload.php';

use SimpleMVC\Core\Application;

\SimpleMVC\Core\Env::load();

if ($_ENV['APP_ENV'] === 'dev' || $_ENV['APP_ENV'] === 'test' || $_ENV['APP_ENV'] === 'local') {
    if ($_ENV['APP_PRETTY_EXCEPTIONS'] ?? false) {
        $whoops = new \Whoops\Run;
        $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
        $whoops->register();
    }
    define('APP_START_TIME', microtime(true));
}

$app = new Application(PATH_CONFIG, PATH_CACHE);
$app->run();
