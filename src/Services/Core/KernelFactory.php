<?php

namespace App\Services\Core;

use App\Controllers\ErrorController;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\PsrHttpMessage\ArgumentValueResolver\PsrServerRequestResolver;
use Symfony\Bridge\PsrHttpMessage\EventListener\PsrResponseListener;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\BackedEnumValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\Controller\ContainerControllerResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\EventListener\ErrorListener;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Routing\RouterInterface;

class KernelFactory
{
    private EventDispatcherInterface $dispatcher;
    private LoggerInterface $logger;
    private RouterInterface $router;

    public function __construct(
        readonly private ContainerInterface $container,
        readonly private array $customEventSubscribers = []
    ) {
        $this->dispatcher = $this->container->get(EventDispatcherInterface::class);
        $this->logger = $this->container->get(LoggerInterface::class);
        $this->router = $this->container->get(RouterInterface::class);
    }

    public function make(): HttpKernel
    {
        $this->subscribe();

        $controllerResolver = $this->getControllerResolver();
        $argumentResolver = $this->getArgumentResolver();

        return new HttpKernel($this->dispatcher, $controllerResolver, new RequestStack(), $argumentResolver);
    }

    private function subscribe(): void
    {
        $this->dispatcher->addSubscriber(new RouterListener($this->router, new RequestStack()));
        $this->dispatcher->addSubscriber(new ErrorListener(ErrorController::class, $this->logger));
        $this->dispatcher->addSubscriber(new PsrResponseListener());

        foreach ($this->customEventSubscribers as $subscriber) {
            $this->dispatcher->addSubscriber($this->container->get($subscriber));
        }
    }

    private function getControllerResolver(): ControllerResolverInterface
    {
        return new ContainerControllerResolver($this->container);
    }

    private function getArgumentResolver(): ArgumentResolverInterface
    {
        $argumentValueResolvers = array_merge(
            [new BackedEnumValueResolver(), $this->container->get(PsrServerRequestResolver::class)],
            ArgumentResolver::getDefaultArgumentValueResolvers()
        );
        return new ArgumentResolver(null, $argumentValueResolvers);
    }
}