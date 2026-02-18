<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Http\CORS;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment as Twig;

readonly class IndexController
{
    public function __construct(
        private Twig $view,
        private UrlGeneratorInterface $url,
        private LoggerInterface $log
    ) {}

    #[Route('/', name: 'index')]
    public function __invoke(Request $request): Response
    {
        $view = $this->view->render('bagatelle.html.twig', [
            'title' => $this->greet(),
        ]);
        return new Response($view);
    }

    #[Route('/example', name: 'example', methods: ['GET'])]
    #[CORS]
    public function example(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        /*
         * Example route demonstrating:
         * Using PSR-7 instead of Symfony HttpFoundation
         * Applying the CORS middleware
         * Exceptions and writing to the log
         * Generating URLs
         */
        $name = $request->getQueryParams()['name'] ?? 'world';
        $limit = 10;

        if (strlen($name) > $limit) {
            $this->log->error("Name {name} exceeds string length $limit", ['name' => $name]);
            throw new BadRequestHttpException('The provided name is too long!');
        }

        $url = $this->url->generate('example', referenceType: UrlGeneratorInterface::ABSOLUTE_URL);
        $response->getBody()->write("Hello $name, you have reached `$url`.");
        return $response->withHeader('Content-Type', 'text/plain');
    }

    private function greet(): string
    {
        $greetings = [
            'Hello!', 'Hi!', 'Hey!', 'Yo!', 'Hiya!',
            "How's everything?", 'How are you?', "How's it going?", "What's up?", 'Howdy!',
            'Greetings!', 'Welcome!', 'Nice to see you!', 'Long time no see!', 'How have you been?',
            'Good to see you!', 'Pleased to meet you!', 'How do you do?', 'Hey there!', "What's new?",
        ];
        return $greetings[array_rand($greetings)];
    }
}
