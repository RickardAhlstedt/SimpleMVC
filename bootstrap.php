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

require_once __DIR__ . '/vendor/autoload.php';

use SimpleMVC\Core\Application;

\SimpleMVC\Core\Env::load();

$app = new Application(PATH_CONFIG, PATH_CACHE);
$app->run();