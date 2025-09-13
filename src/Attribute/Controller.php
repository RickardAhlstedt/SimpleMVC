<?php

declare(strict_types=1);

namespace SimpleMVC\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Controller
{
    public function __construct()
    {
    }
}
