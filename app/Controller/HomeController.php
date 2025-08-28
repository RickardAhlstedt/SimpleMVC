<?php

declare(strict_types=1);

namespace App\Controller;

use SimpleMVC\Attribute\Controller;
use SimpleMVC\Attribute\Route;
use SimpleMVC\Core\HTTP\RequestStack;
use SimpleMVC\Core\HTTP\Response;

use SimpleMVC\Database\EntityManager;

#[Controller]
class HomeController extends \SimpleMVC\Core\HTTP\AbstractController
{

    #[Route(
        name: 'home_index',
        path: '/',
        method: 'GET'
    )]
    public function index(RequestStack $request) : Response
    {
        // echo '<pre>';
        // $em = new EntityManager($this->database);
        // $userRepo = $em->getRepository(\App\Entities\User::class);

        // $newUser = new \App\Entities\User();
        // $newUser->username = 'johndoe';
        // $newUser->email = 'john@doe.com';
        // $em->persist($newUser);
        // var_dump($newUser);

        // $fetched = $userRepo->find($newUser->getId());
        // var_dump($fetched);
        // echo '</pre>';


        return new Response($this->render('home.html.twig', ['name' => 'World']), 200);
    }
}