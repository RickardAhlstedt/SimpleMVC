<?php

namespace App\Entities;
use SimpleMVC\Database\BaseModel;
use SimpleMVC\Attribute\Database\{Table, Column};


#[Table(name: 'users')]
class User extends BaseModel
{

    #[Column(name: 'username')]
    public string $username;

    #[Column(name: 'email')]
    public string $email;

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

}