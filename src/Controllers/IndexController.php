<?php

namespace App\Controllers;

use App\Services\GreetingInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment as Template;

readonly class IndexController
{
    public function __construct(
        private Template $view,
        private GreetingInterface $greeter
    ) {}

    #[Route('/', name: 'index')]
    function __invoke(Request $request): Response
    {
        $view = $this->view->render('welcome.html.twig', [
            'greeting' => $this->greeter->greet()
        ]);
        return new Response($view);
    }
}