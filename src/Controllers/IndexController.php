<?php

declare(strict_types=1);

namespace App\Controllers;

use tthe\Bagatelle\Http\BasicAuth;
use tthe\Bagatelle\Http\CORS;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use tthe\Bagatelle\Misc\Greeter;
use Twig\Environment as Twig;

readonly class IndexController
{
    public function __construct(
        private Greeter $greeter,
        private Twig $view,
        private UrlGeneratorInterface $url
    ) {}

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $html = $this->view->render('bagatelle.html.twig', [
            'title' => $this->greeter->greet(),
        ]);
        return new Response($html);
    }

    #[Route('/example', name: 'example', methods: ['GET'])]
    #[CORS]
    public function example(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        /*
         * Example resource demonstrating:
         * - Using PSR-7 instead of Symfony HttpFoundation
         * - Applying the CORS middleware
         * - Generating URLs
         */
        $url = $this->url->generate('example', referenceType: UrlGeneratorInterface::ABSOLUTE_URL);
        $name = $request->getQueryParams()['name'] ?? 'world';
        $response->getBody()->write("Hello $name, you have reached `$url`.");
        return $response->withHeader('Content-Type', 'text/plain');
    }

    #[Route('/user', name: 'user_example', methods: ['GET'])]
    #[BasicAuth]
    public function user_example(Request $request): Response
    {
        /*
         * Example resource demonstrating the Basic Authentication middleware.
         */
        $user = $request->attributes->get('auth.user');
        return new Response("Authenticated as '{$user['id']}'.", headers: ['Content-Type' => 'text/plain']);
    }
}
