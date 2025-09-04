<?php

namespace SimpleMVC\Security;

use SimpleMVC\Core\Config;

class PasswordHasher
{

    private string $algo;
    private array $options;
    private string $pepper;

    public function __construct(string|null $pepper = null)
    {

        if ($pepper === null) {
            $this->pepper = $_ENV['APP_SECRET'];
        } else {
            $this->pepper = $pepper;
        }
        $config = Config::getInstance()->get('password');
        $algo = $config['algo'] ?? PASSWORD_BCRYPT;
        if (is_string($algo) && defined($algo)) {
            $algo = constant($algo);
        }
        $this->algo = $algo;
        $this->options = $config['options'] ?? [];

    }

    public function hash(string $password): string
    {
        $peppered = hash_hmac("sha256", $password, $this->pepper);
        return password_hash($peppered, $this->algo, $this->options);
    }

    public function verify(string $password, string $hashedPassword): bool
    {
        $peppered = hash_hmac("sha256", $password, $this->pepper);
        return password_verify($peppered, $hashedPassword);
    }

    public function needsRehash(string $password): bool
    {
        return password_needs_rehash($password, $this->algo, $this->options);
    }

}
