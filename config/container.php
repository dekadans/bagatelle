<?php
/*
 * Configures and returns a PSR-11 compliant dependency injection container.
 */

use DI\ContainerBuilder;
use Psr\EventDispatcher\EventDispatcherInterface as PsrEventDispatcherInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as ContractsEventDispatcherInterface;
use Twig\Environment as Twig;
use Twig\Loader\FilesystemLoader as TwigFilesystemLoader;
use function DI\autowire;
use function DI\get;

$containerBuilder = new ContainerBuilder();

// Core dependencies
$containerBuilder->addDefinitions([
    FileLocatorInterface::class => function() {
        return new FileLocator(__DIR__.'/..');
    },
    EventDispatcherInterface::class => autowire(EventDispatcher::class),
    ContractsEventDispatcherInterface::class => get(EventDispatcherInterface::class),
    PsrEventDispatcherInterface::class => get(EventDispatcherInterface::class),
    Twig::class => function (FileLocatorInterface $locator) {
        $templateDir = $locator->locate('templates');
        return new Twig(new TwigFilesystemLoader($templateDir));
    }
]);

// Example service. Feel free to remove this :)
$containerBuilder->addDefinitions([
    \App\Services\Greet\GreetingInterface::class =>
        autowire(\App\Services\Greet\GreetingRandomizer::class)
]);

return $containerBuilder->build();