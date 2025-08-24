<?php

declare(strict_types=1);

namespace App\Controller;

use SimpleMVC\Attribute\Controller;
use SimpleMVC\Attribute\Route;
use SimpleMVC\Core\HTTP\RequestStack;
use SimpleMVC\Core\HTTP\Response;

#[Controller]
class HomeController extends \SimpleMVC\Core\HTTP\AbstractController
{

    public function __construct(RequestStack $request, \SimpleMVC\Templating\Templating $templating)
    {
        parent::__construct($request, $templating);
    }

    #[Route(
        name: 'home_index',
        path: '/',
        method: 'GET'
    )]
    public function index(RequestStack $request) : Response
    {
        return new Response($this->render('home.html.twig', ['name' => 'World']), 200);
    }
}