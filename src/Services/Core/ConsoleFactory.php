<?php

namespace App\Services\Core;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\CommandLoader\ContainerCommandLoader;
use Symfony\Component\Console\EventListener\ErrorListener;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ConsoleFactory
{
    private EventDispatcherInterface $dispatcher;
    private LoggerInterface $logger;

    public function __construct(
        readonly private string $name,
        readonly private ContainerInterface $container,
        readonly private array $commands
    ) {
        $this->dispatcher = $this->container->get(EventDispatcherInterface::class);
        $this->logger = $this->container->get(LoggerInterface::class);
    }

    public function make(): Application
    {
        $this->subscribe();
        $commandLoader = $this->getCommandLoader();

        $app = new Application($this->name);
        $app->setCommandLoader($commandLoader);
        $app->setDispatcher($this->dispatcher);
        return $app;
    }

    private function subscribe(): void
    {
        $this->dispatcher->addSubscriber(new ErrorListener($this->logger));
    }

    private function getCommandLoader(): CommandLoaderInterface
    {
        $commandMap = [];
        foreach ($this->commands as $commandClass) {
            $commandAttribute = (new ReflectionClass($commandClass))->getAttributes(AsCommand::class);
            if (!$commandAttribute) {
                continue;
            }

            $name = $commandAttribute[0]->newInstance()->name;
            $commandMap[$name] = $commandClass;
        }

        return new ContainerCommandLoader($this->container, $commandMap);
    }
}