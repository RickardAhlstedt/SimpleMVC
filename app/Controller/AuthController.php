<?php

namespace App\Controller;

use App\Service\UserRegistrationService;
use SimpleMVC\Auth\AuthManager;
use SimpleMVC\Core\HTTP\AbstractController;
use SimpleMVC\Core\HTTP\RequestStack;

#[Controller]
class AuthController extends AbstractController
{
    public function __construct(
        protected RequestStack $requestStack,
        protected UserRegistrationService $userRegistrationService,
        protected AuthManager $authManager,
    )
    {}
}
