<?php

namespace SimpleMVC\Auth;

use SimpleMVC\Database\BaseModel;

interface AuthInterface
{
    public function login(string $username, string $password): bool;
    public function logout(): void;
    public function check(): bool;
    public function user(): ?BaseModel;
}
