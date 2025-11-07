<?php

namespace App\Services\Core;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\EventListener\ErrorListener;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ConsoleFactory
{
    private EventDispatcherInterface $dispatcher;
    private LoggerInterface $logger;
    private CommandLoaderInterface $loader;

    public function __construct(
        readonly private string $name,
        readonly private ContainerInterface $container
    ) {
        $this->dispatcher = $this->container->get(EventDispatcherInterface::class);
        $this->logger = $this->container->get(LoggerInterface::class);
        $this->loader = $this->container->get(CommandLoaderInterface::class);
    }

    public function make(): Application
    {
        $this->dispatcher->addSubscriber(new ErrorListener($this->logger));

        $app = new Application($this->name);
        $app->setCommandLoader($this->loader);
        $app->setDispatcher($this->dispatcher);
        return $app;
    }
}