<?php

namespace SimpleMVC\Model;
interface Localized
{
    public static function getLocaleTable(string $locale): string;
}