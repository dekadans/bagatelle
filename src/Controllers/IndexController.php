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
    public function index(Request $request): Response
    {
        /*
         * Default welcome page demonstrating:
         * - Accessing query parameters
         * - Logging and HTTP exceptions
         * - Rendering and returning HTML
         */
        $greetings = [
            'Hello!', 'Hi!', 'Hey!', 'Yo!', 'Hiya!',
            "How's everything?", 'How are you?', "How's it going?", "What's up?", 'Howdy!',
            'Greetings!', 'Welcome!', 'Nice to see you!', 'Long time no see!', 'How have you been?',
            'Good to see you!', 'Pleased to meet you!', 'How do you do?', 'Hey there!', "What's new?",
        ];

        $i = $request->query->has('i') ? $request->query->getInt('i') : array_rand($greetings);

        if ($i >= count($greetings)) {
            $this->log->warning('Unable to handle request for greeting number {greeting_index}.', ['greeting_index' => $i]);
            throw new BadRequestHttpException("Provided greeting index $i is out of bounds.");
        }

        $html = $this->view->render('bagatelle.html.twig', [
            'title' => $greetings[$i],
        ]);
        return new Response($html);
    }

    #[Route('/example', name: 'example', methods: ['GET'])]
    #[CORS]
    public function example(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        /*
         * Example page demonstrating:
         * - Using PSR-7 instead of Symfony HttpFoundation
         * - Applying the CORS middleware
         * - Generating URLs
         */
        $url = $this->url->generate('example', referenceType: UrlGeneratorInterface::ABSOLUTE_URL);
        $name = $request->getQueryParams()['name'] ?? 'world';
        $response->getBody()->write("Hello $name, you have reached `$url`.");
        return $response->withHeader('Content-Type', 'text/plain');
    }
}
