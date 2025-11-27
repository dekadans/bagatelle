<?php declare(strict_types=1);

namespace App\Controllers;

use App\Services\Auth\Auth;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment as Twig;

// All routes in this controller have paths prefixed with "/examples"
#[Route('/examples')]
class ExampleController
{
    public function __construct(
        private Twig $twig,
        private UrlGeneratorInterface $url,
        private LoggerInterface $logger
    ) {
        // Services can be injected in the constructor from the dependency injection container.
        // Classes are autowired if possible, while interfaces and more complex initializations
        // can be bound in config/container.php.
    }

    #[Route(methods: ['GET'])]
    public function index(): Response
    {
        // Logging, HTML templating and URL generation is prepared, with services ready to be injected.

        $this->logger->info('Example log message :)');

        $url = $this->url->generate('param-example', ['category' => 'demo', 'page' => 42, 'message' => 'Hey']);

        $html = $this->twig->render('main.html.twig', [
            'title' => 'Example page.',
            'message' => "Using UrlGeneratorInterface, it's very easy to generate URLs like $url"
        ]);

        return new Response($html);
    }

    #[Route('/params/category/{category}/page/{page<\d+>}', name: 'param-example', methods: ['GET'])]
    public function params(Request $request, string $category, int $page): Response
    {
        // Most features of Symfony routing are supported, like path parameters.
        // Type-hint Request to access other request details like query parameters.

        $message = $request->query->get('message', 'Hello World!');
        $response = new Response("Category: $category, Page: $page, Message: $message");
        $response->headers->set('Content-Type', 'text/plain');
        return $response;
    }

    #[Route('/psr', name: 'psr-example', methods: ['GET'])]
    public function psr(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        // It's also possible to use PSR-7 messages instead of the Symfony-native HttpFoundation.
        // Just type-hint both the request and response in the method using the PSR interfaces.

        $message = $request->getQueryParams()['message'] ?? 'Hello World!';
        $response->getBody()->write('Response using PSR-7: ' . $message);
        return $response->withHeader('Content-Type', 'text/plain');
    }

    #[Route('/json', name: 'json-example', methods: ['POST'])]
    public function json(Request $request): JsonResponse
    {
        // JSON processing is available through Request::toArray() and JsonResponse.
        // Various HTTP exceptions can be found in the Symfony\Component\HttpKernel\Exception namespace.

        $json = $request->toArray();

        if (empty($json['message'])) {
            throw new UnprocessableEntityHttpException('Mandatory property "message" was not included.');
        }

        return new JsonResponse([
            'status' => true,
            'result' => "Message from JSON payload: {$json['message']}"
        ]);
    }

    #[Auth]
    #[Route('/auth', name: 'auth-example')]
    public function auth(): Response
    {
        // The #[Auth] attribute will activate the authentication logic in AuthenticationSubscriber.
        // You can make your own route attributes by implementing RouteDecoratorInterface.

        return new Response('Protected content');
    }
}
