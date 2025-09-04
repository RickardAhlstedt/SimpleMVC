<?php
namespace App\Service;

use App\Entities\User;
use SimpleMVC\Database\EntityManager;
use SimpleMVC\Security\PasswordHasher;

class UserRegistrationService
{
    private EntityManager $em;
    private PasswordHasher $hasher;

    public function __construct(EntityManager $em, PasswordHasher $hasher)
    {
        $this->em = $em;
        $this->hasher = $hasher;
    }

    public function register(string $username, string $email, string $plainPassword): User
    {
        $user = new User();
        $user->username = $username;
        $user->email = $email;
        $user->password = $this->hasher->hash($plainPassword);
        $user->created_at = date('c');

        $this->em->persist($user);
        return $user;
    }
}
