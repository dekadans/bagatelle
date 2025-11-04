<?php

namespace App\Services\Auth;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment as Template;

readonly class AuthenticationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private Template $view
    ){}

    /**
     * The default authentication provider.
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
        $this->logger->warning('Authentication remains unimplemented, add your logic to '.__FILE__);

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
        $errorPage = $this->view->render('error.html.twig', [
            'title' => 'Unauthenticated',
            'message' => 'This page requires authentication, which has not been provided or was incorrect.'
        ]);
        return new Response($errorPage, 401);
    }

    public function checkAuthOnRequestEvent(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $hasAuth = $request->attributes->get(RequiresAuth::REQUEST_ATTRIBUTE, false);

        if (!$hasAuth) {
            return;
        }

        if (!$this->performAuthentication($request)) {
            $response = $this->makeUnauthenticatedResponse($request);
            $event->setResponse($response);
        }
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::REQUEST => 'checkAuthOnRequestEvent'];
    }
}