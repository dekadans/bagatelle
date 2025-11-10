<?php

namespace App\Services\Core;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ConsoleFactory
{
    private EventDispatcherInterface $dispatcher;
    private CommandLoaderInterface $loader;

    public function __construct(
        readonly private string $name,
        readonly private ContainerInterface $container
    ) {
        $this->dispatcher = $this->container->get(EventDispatcherInterface::class);
        $this->loader = $this->container->get(CommandLoaderInterface::class);
    }

    public function make(): Application
    {
        $subscribers = array_merge(
            $this->container->get('bagatelle.console.subscribers'),
            $this->container->get('app.console.subscribers')
        );

        foreach ($subscribers as $subscriber) {
            $this->dispatcher->addSubscriber($subscriber);
        }

        $app = new Application($this->name);
        $app->setCommandLoader($this->loader);
        $app->setDispatcher($this->dispatcher);
        return $app;
    }
}