<?php

namespace App\Services\Core;

use Psr\Container\ContainerInterface;
use Symfony\Bridge\PsrHttpMessage\ArgumentValueResolver\PsrServerRequestResolver;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\BackedEnumValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\Controller\ContainerControllerResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\HttpKernel;

class KernelFactory
{
    private EventDispatcherInterface $dispatcher;

    public function __construct(
        readonly private ContainerInterface $container
    ) {
        $this->dispatcher = $this->container->get(EventDispatcherInterface::class);
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
        $subscribers = array_merge(
            $this->container->get('bagatelle.http.subscribers'),
            $this->container->get('app.http.subscribers')
        );

        foreach ($subscribers as $subscriber) {
            $this->dispatcher->addSubscriber($subscriber);
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