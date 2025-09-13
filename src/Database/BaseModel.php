<?php

namespace SimpleMVC\Database;

use SimpleMVC\Attributes\Database\{Table, Column};

#[Entity]
abstract class BaseModel
{
    #[Column(name: 'id', type: 'int', primary: true, autoincrement: true)]
    public ?int $id = null;
}
