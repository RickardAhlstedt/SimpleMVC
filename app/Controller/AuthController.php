<?php

namespace App\Controller;

use App\Entities\User;
use App\Service\UserRegistrationService;
use SimpleMVC\Attribute\Controller;
use SimpleMVC\Attribute\Route;
use SimpleMVC\Auth\AuthManager;
use SimpleMVC\Core\HTTP\AbstractController;
use SimpleMVC\Core\HTTP\RequestStack;
use SimpleMVC\Core\HTTP\Response;

#[Controller]
class AuthController extends AbstractController
{
    #[Route(
        name: 'login',
        path: '/login',
        method: 'GET'
    )]
    public function login(RequestStack $request): Response
    {
        return new Response($this->templating->render('auth/login.html.twig'));
    }

    #[Route(
        name: 'loginAction',
        path: '/login',
        method: 'POST',
    )]
    public function loginAction(RequestStack $request, AuthManager $authManager): Response
    {
        $username = $request->get('username');
        $password = $request->get('password');

        if ($authManager->login($username, $password)) {
            /** @var User $user */
            $user = $authManager->user();
            return new Response($this->templating->render('home.html.twig', ['name' => $user->username]), 200);
        }
        return new Response($this->templating->render('auth/login.html.twig', ['error' => 'Invalid credentials']), 400);
    }

    #[Route(
        name: 'register',
        path: '/register',
        method: 'GET'
    )]
    public function register(RequestStack $request): Response
    {
        return new Response($this->templating->render('auth/register.html.twig'));
    }

    #[Route(
        name: 'registerAction',
        path: '/register',
        method: 'POST',
    )]
    public function registerAction(RequestStack $request, UserRegistrationService $registrationService): Response
    {
        $flash = [];
        $username = $request->get('username');
        $password = $request->get('password');
        $email = $request->get('email');
        $user = $registrationService->register($username, $email, $password);
        $flash['success'] = 'Registration was successful!';
        return new Response($this->templating->render('auth/register.html.twig', ['message' => $flash]), 200);
    }

    #[Route(
        name: 'logout',
        path: '/logout',
        method: 'GET'
    )]
    public function logout(AuthManager $authManager): Response
    {
        $authManager->logout();
        return new Response($this->templating->render('auth/login.html.twig'));
    }

}
