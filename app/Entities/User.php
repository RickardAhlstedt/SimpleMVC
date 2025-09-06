<?php

namespace App\Entities;
use SimpleMVC\Attributes\Database\Column;
use SimpleMVC\Attributes\Database\Table;
use SimpleMVC\Database\BaseModel;


#[Table(name: 'users')]
class User extends BaseModel
{

    #[Column(name: 'username')]
    public string $username;

    #[Column(name: 'password')]
    public string $password;

    #[Column(name: 'email')]
    public string $email;

    #[Column(name: 'created_at')]
    public string $created_at;

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

}
