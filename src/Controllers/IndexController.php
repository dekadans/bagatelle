<?php

namespace App\Controllers;

use App\Services\Greet\GreetingInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class IndexController
{
    #[Route('/', name: 'index')]
    function __invoke(Request $request, GreetingInterface $greeter): Response
    {
        return new Response($greeter->greet());
    }
}