<?php

declare(strict_types=1);

namespace SimpleMVC\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Command
{
    public string $name;
    public string $description;

    public function __construct(string $name, string $description = '')
    {
        $this->name = $name;
        $this->description = $description;
    }
}