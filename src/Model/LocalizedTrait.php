<?php

namespace SimpleMVC\Model;

trait LocalizedTrait
{
    public static function getLocaleTable(string $locale): string
    {
        // Default: table name + '_' + locale
        $base = strtolower((new \ReflectionClass(static::class))->getShortName()) . 's';
        return $base . '_' . $locale;
    }
}