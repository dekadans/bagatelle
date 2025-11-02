<?php

namespace App\Services\Auth;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
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

    public function handleAuthentication(RequestEvent $event): void
    {
        $attributes = $event->getRequest()->attributes;
        if (!$attributes->get('_auth', false)) {
            return;
        }

        $this->logger->warning('Controller action requires authentication, which has not been implemented.', [
            'controller' => $attributes->get('_controller')
        ]);

        $errorPage = $this->view->render('error.html.twig', [
            'title' => 'Unauthenticated',
            'message' => 'This page requires authentication, which has not been provided or was incorrect.'
        ]);

        $event->setResponse(new Response($errorPage, 401));
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::REQUEST => 'handleAuthentication'];
    }
}