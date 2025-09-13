<?php

declare(strict_types=1);

namespace SimpleMVC\Support;

class EnvVars
{
    public static function getEnvVars(): array
    {
        return [
            '%PATH_ROOT%'     => defined('PATH_ROOT') ? PATH_ROOT : '',
            '%PATH_CORE%'     => defined('PATH_CORE') ? PATH_CORE : '',
            '%PATH_APP%'      => defined('PATH_APP') ? PATH_APP : '',
            '%PATH_CONFIG%'   => defined('PATH_CONFIG') ? PATH_CONFIG : '',
            '%PATH_CACHE%'    => defined('PATH_CACHE') ? PATH_CACHE : '',
            '%PATH_TEMPLATE%' => defined('PATH_TEMPLATE') ? PATH_TEMPLATE : '',
            '%PATH_PUBLIC%'   => defined('PATH_PUBLIC') ? PATH_PUBLIC : '',
            '%PATH_VENDOR%'   => defined('PATH_VENDOR') ? PATH_VENDOR : '',
            '%PATH_LOG%'      => defined('PATH_LOG') ? PATH_LOG : '',
            '%PATH_VAR%'      => defined('PATH_VAR') ? PATH_VAR : '',
            '%PATH_VAR_CONFIG%' => defined('PATH_VAR_CONFIG') ? PATH_VAR_CONFIG : '',
            '%PATH_MIGRATIONS%' => defined('PATH_MIGRATIONS') ? PATH_MIGRATIONS : '',
        ];
    }

    public static function get(string $key, $default = null)
    {
        $vars = self::getEnvVars();
        return $vars[$key] ?? $default;
    }
}
