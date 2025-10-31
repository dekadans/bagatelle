<?php
/*
 * Configures and returns a PSR-11 compliant dependency injection container.
 */

use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use Psr\EventDispatcher\EventDispatcherInterface as PsrEventDispatcherInterface;
use Psr\Log\LoggerInterface;
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
    Twig::class => function () {
        $cacheDir = __DIR__.'/../'.$_ENV['TWIG_CACHE_DIR'];
        $templateDir = __DIR__.'/../templates';
        $options = $_ENV['TWIG_CACHE'] ? ['cache' => $cacheDir] : [];
        return new Twig(new TwigFilesystemLoader($templateDir), $options);
    },
    LoggerInterface::class => function () {
        $stream = __DIR__.'/../'.$_ENV['LOG_STREAM'];
        $level = Level::fromName($_ENV['LOG_LEVEL']);
        return new Logger('default', [new StreamHandler($stream, $level)], [new PsrLogMessageProcessor()]);
    }
]);

// Example service. Feel free to remove this :)
$containerBuilder->addDefinitions([
    \App\Services\Greet\GreetingInterface::class =>
        autowire(\App\Services\Greet\GreetingRandomizer::class)
]);

if ($_ENV['DI_CACHE']) {
    $containerBuilder->enableCompilation(__DIR__.'/../'.$_ENV['DI_CACHE_DIR']);
}

return $containerBuilder->build();