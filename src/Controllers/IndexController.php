<?php

declare(strict_types=1);

namespace App\Controllers;

use tthe\Bagatelle\Http\Attribute\Get;
use Symfony\Component\HttpFoundation\Response;
use tthe\Bagatelle\Misc\Greeter;
use Twig\Environment as Twig;

readonly class IndexController
{
    public function __construct(
        private Greeter $greeter,
        private Twig $view,
    ) {}

    #[Get('/', 'index')]
    public function index(): Response
    {
        $html = $this->view->render('welcome.html.twig', [
            'title' => $this->greeter->greet(),
        ]);
        return new Response($html);
    }
}
