<?php

namespace App\Controllers;

use App\Services\Auth\Auth;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Psr\Http\Message\ResponseInterface as PsrResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/examples')]
class ExampleController
{
    public function __construct(
        private UrlGeneratorInterface $url
    )
    {

    }

    #[Route]
    public function index(): JsonResponse
    {
        return new JsonResponse([
            $this->url->generate('auth-example')
        ]);
    }

    // Query

    // Path

    #[Auth]
    #[Route('/auth', name: 'auth-example')]
    public function auth(): Response
    {
        return new Response('Protected content');
    }

    #[Route('/psr', name: 'psr-example')]
    public function psr(PsrRequest $request, PsrResponse $response): PsrResponse
    {
        // Response as argument

        $message = $request->getQueryParams()['message'] ?? 'Hello World!';

        $response->getBody()->write('Response using PSR-7: ' . $message);
        return $response->withHeader('Content-Type', 'text/plain');
    }
}