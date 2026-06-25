<?php

declare(strict_types=1);

namespace App\Controllers;

use Composer\InstalledVersions;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Request;
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
    public function index(Request $request): Response
    {
        /*
         * Controller action using Symfony's HttpFoundation.
         */
        $html = $this->view->render('welcome.html.twig', [
            'greeting' => $this->greeter->greet(),
        ]);
        return new Response($html);
    }

    #[Get('/deps', 'deps')]
    public function example(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        /*
         * Controller action using PSR message interfaces.
         */
        $dependencies = implode("\n", InstalledVersions::getInstalledPackagesByType('library'));
        $text = "# Installed Dependencies\n\n$dependencies";

        $response->getBody()->write($text);
        return $response->withHeader('Content-Type', 'text/plain');
    }
}
