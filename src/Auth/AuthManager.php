<?php

namespace SimpleMVC\Auth;

use App\Entities\User;
use SimpleMVC\Auth\AuthInterface;
use SimpleMVC\Core\Container;
use SimpleMVC\Database\BaseModel;
use SimpleMVC\Database\EntityManager;
use SimpleMVC\Database\Repository;
use SimpleMVC\Security\PasswordHasher;

class AuthManager implements AuthInterface
{

    private Repository $userRepository;
    private ?BaseModel $currentUser = null;

    public function __construct(EntityManager $entityManager)
    {
        $this->userRepository = $entityManager->getRepository(User::class);

        if (isset($_SESSION['user_id'])) {
            $this->currentUser = $this->userRepository->find($_SESSION['user_id']);
        }
    }

    public function login(string $username, string $password): bool
    {
        $user = $this->userRepository->findOneBy(['username' => $username]);
        if (!$user) {
            return false;
        }

        $hasher = Container::getInstance()?->get(\SimpleMVC\Security\PasswordHasher::class);

        if (!$hasher) {
            return false;
        }

        if (!$hasher->verify($password, $user->password)) {
            return false;
        }

        $_SESSION['user_id'] = $user->id;
        $this->currentUser = $user;
        return true;
    }

    public function logout(): void
    {
        unset($_SESSION['user_id']);
        $this->currentUser = null;
    }

    public function check(): bool
    {
        return $this->currentUser !== null;
    }

    public function user(): ?BaseModel
    {
        return $this->currentUser;
    }
}
