<?php

namespace App\Controllers;

use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\AcceptHeader;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Template;

/**
 * Default error handler.
 * Will display an error page to the user for all uncaught exceptions.
 */
readonly class ErrorController
{
    public function __construct(
        private Template $view
    ) {}

    function __invoke(Request $request, FlattenException $exception): Response
    {
        $exceptionDetails = (bool) $_ENV["BAGATELLE_DETAILED_ERRORS"];

        $contentType = $this->negotiateContentType($request);

        if (str_contains($contentType, 'json')) {
            $data = $this->asJSON($exception, $exceptionDetails);
            return new JsonResponse($data, headers: [
                'Content-Type' => $contentType
            ]);
        } else {
            $data = $this->asHTML($exception, $exceptionDetails);
            return new Response($data);
        }
    }

    private function negotiateContentType(Request $request): string
    {
        $acceptable = [
            'text/html',
            'application/problem+json',
            'application/json'
        ];
        $acceptHeader = AcceptHeader::fromString(
            $request->headers->get('Accept') ?? '*/*'
        );
        $quality = fn($type) => $acceptHeader->get($type)?->getQuality() ?? 0;

        usort($acceptable, fn($a, $b) => $quality($b) <=> $quality($a));
        return $acceptable[0];
    }

    private function asHTML(FlattenException $exception, bool $details): string
    {
        return $this->view->render('error.html.twig', [
            'statusCode' => $exception->getStatusCode(),
            'statusText' => $exception->getStatusText(),
            'details' => $details ? $exception->toArray() : null
        ]);
    }

    private function asJSON(FlattenException $exception, bool $details): array
    {
        $data = [
            'type' => 'about:blank',
            'status' => $exception->getStatusCode(),
            'title' => $exception->getStatusText(),
            'detail' => 'An error occurred when processing the request.'
        ];

        if ($details) {
            $data['detail'] = $exception->getMessage();

            $data['exceptions'] = [];
            foreach ($exception->toArray() as $ex) {
                $filtered = array_filter(
                    $ex,
                    fn($key) => in_array($key, ['class', 'trace']),
                    ARRAY_FILTER_USE_KEY
                );
                $filtered['trace'] = array_map(
                    fn($tr) => array_filter(
                        $tr,
                        fn($key) => in_array($key, ['class', 'type', 'function', 'file', 'line']),
                        ARRAY_FILTER_USE_KEY
                    ),
                    $filtered['trace']
                );
                $data['exceptions'][] = $filtered;
            }
        }

        return $data;
    }
}