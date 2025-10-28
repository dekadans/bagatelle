<?php

namespace tthe\Bagatelle;

use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Default error handler.
 * Will catch all uncaught exceptions and display an error page to the user.
 */
class ErrorHandler
{
    function __invoke(Request $request, FlattenException $exception): Response
    {
        $message = "{$exception->getStatusCode()} {$exception->getStatusText()}";
        if ($_ENV["BAGATELLE_DETAILED_ERRORS"]) {
            $message .= "\n\n" . print_r($exception->toArray(), true);
        }

        return new Response($message, headers: [
            'Content-Type' => 'text/plain'
        ]);
    }
}