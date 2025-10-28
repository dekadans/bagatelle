<?php

namespace tthe\Bagatelle;

use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Template;

/**
 * Default error handler.
 * Will catch all uncaught exceptions and display an error page to the user.
 */
readonly class ErrorHandler
{
    public function __construct(private Template $view)
    {

    }

    function __invoke(Request $request, FlattenException $exception): Response
    {
        $exceptionDetails = (bool) $_ENV["BAGATELLE_DETAILED_ERRORS"];
        $view = $this->view->render('error.html.twig', [
            'statusCode' => $exception->getStatusCode(),
            'statusText' => $exception->getStatusText(),
            'details' => $exceptionDetails ? print_r($exception->toArray(), true) : null
        ]);

        return new Response($view);
    }
}