<?php

namespace SimpleMVC\Attributes\Database;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Column
{
    public function __construct(
        public string $name,
        public bool $primary = false,
        public bool $autoIncrement = false
    ) {
    }
}
