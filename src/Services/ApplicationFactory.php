<?php

namespace App\Services;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\Controller\ContainerControllerResolver;
use Symfony\Component\HttpKernel\HttpKernel;

class ApplicationFactory
{
    private EventDispatcherInterface $dispatcher;

    public function __construct(
        readonly private ContainerInterface $container
    ) {
        $this->dispatcher = $this->container->get(EventDispatcherInterface::class);
    }

    public function makeHttp(): HttpKernel
    {
        $this->subscribe('bagatelle.http.subscribers', 'app.http.subscribers');

        $controllerResolver = new ContainerControllerResolver($this->container);
        $argumentResolver = $this->container->get(ArgumentResolverInterface::class);

        return new HttpKernel($this->dispatcher, $controllerResolver, new RequestStack(), $argumentResolver);
    }

    public function makeConsole(string $name): Application
    {
        $this->subscribe('bagatelle.console.subscribers', 'app.console.subscribers');

        $loader = $this->container->get(CommandLoaderInterface::class);

        $app = new Application($name);
        $app->setCommandLoader($loader);
        $app->setDispatcher($this->dispatcher);
        return $app;
    }

    private function subscribe(string ...$containerKeys): void
    {
        $subscribers = array_merge(
            ...array_map($this->container->get(...), $containerKeys)
        );

        foreach ($subscribers as $subscriber) {
            $this->dispatcher->addSubscriber($subscriber);
        }
    }
}