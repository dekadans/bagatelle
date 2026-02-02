<?php

namespace App\Middleware;

use App\Routing\AbstractMiddleware;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class Authentication extends AbstractMiddleware
{
    public function __construct(
        private readonly Environment $view,
        private readonly LoggerInterface $logger
    ) {

    }

    public function inbound(Request $request): ?Response
    {
        if (!$this->performAuthentication($request)) {
            return $this->makeUnauthenticatedResponse($request);
        }

        return null;
    }
    /**
     * The default authentication implementation.
     * Return true if authentication is successful, false otherwise.
     *
     * @param Request $request
     * @return bool
     */
    private function performAuthentication(Request $request): bool
    {
        // Replace this with your authentication logic.
        // Optionally also set values on the request:
        //   $request->attributes->set('username', 'John Doe');
        $this->logger->warning('Authentication remains unimplemented, add your logic to ' . __FILE__);

        return false;
    }

    /**
     * Create a response to unauthenticated users.
     *
     * @param Request $request
     * @return Response
     */
    private function makeUnauthenticatedResponse(Request $request): Response
    {
        $errorPage = $this->view->render('bagatelle.html.twig', [
            'page_title' => 'Error - Unauthenticated',
            'title' => 'Unauthenticated',
            'message' => 'This page requires authentication, which has not been provided or was incorrect.'
        ]);
        return new Response($errorPage, 401);
    }
}