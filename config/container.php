<?php
/*
 * Configures and returns a PSR-11 compliant dependency injection container.
 */

use DI\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use function DI\autowire;

$containerBuilder = new ContainerBuilder();

$containerBuilder->addDefinitions([
    FileLocatorInterface::class => function() {
        return new FileLocator(__DIR__.'/..');
    },

    EventDispatcherInterface::class => autowire(EventDispatcher::class)
]);

return $containerBuilder->build();