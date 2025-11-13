<?php

namespace App\Services;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\Controller\ContainerControllerResolver;
use Symfony\Component\HttpKernel\HttpKernel;

/**
 * Bootstraps the application.
 */
class Application
{
    private ContainerInterface $container;
    private EventDispatcherInterface $dispatcher;

    private(set) \Closure $console {
        get {
            $this->console ??= $this->makeConsoleApplication();
            return $this->console;
        }
    }

    private(set) \Closure $http {
        get {
            $this->http ??= $this->makeHttpApplication();
            return $this->http;
        }
    }

    /**
     * Sets up environment variables and the dependency injection container.
     */
    private function boot(): void
    {
        // Load environment variables
        $envPath = dirname(__DIR__, 2);
        $env = Dotenv::createImmutable($envPath);
        try {
            $env->load();
        } catch (InvalidPathException) {
            echo "No .env file found at $envPath";
            exit();
        }

        // Set some PHP runtime settings
        error_reporting((E_ALL & ~E_NOTICE) ? $_ENV["ERROR_DETAILS"] : 0);
        date_default_timezone_set($_ENV['TIMEZONE']);

        // Create container and resolve the event dispatcher
        $this->container = require __DIR__ . '/../../config/container.php';
        $this->dispatcher = $this->container->get(EventDispatcherInterface::class);
    }

    /**
     * Creates the HTTP Kernel object and returns a closure to execute it.
     */
    private function makeHttpApplication(): \Closure
    {
        $this->boot();
        $this->subscribe('bagatelle.http.subscribers', 'app.http.subscribers');

        $controllerResolver = new ContainerControllerResolver($this->container);
        $argumentResolver = $this->container->get(ArgumentResolverInterface::class);

        $kernel = new HttpKernel($this->dispatcher, $controllerResolver, new RequestStack(), $argumentResolver);

        return function() use ($kernel) {
            $request = Request::createFromGlobals();
            $response = $kernel->handle($request);
            $response->prepare($request);
            $response->send();
            $kernel->terminate($request, $response);
        };
    }

    /**
     * Creates the console application handler and returns a closure to execute it.
     */
    private function makeConsoleApplication(): \Closure
    {
        $this->boot();
        $this->subscribe('bagatelle.console.subscribers', 'app.console.subscribers');

        $loader = $this->container->get(CommandLoaderInterface::class);

        $app = new ConsoleApplication($_ENV['CONSOLE_NAME']);
        $app->setCommandLoader($loader);
        $app->setDispatcher($this->dispatcher);

        return function() use ($app) {
            $app->run();
        };
    }

    /**
     * Resolves EventSubscriber instances from one or many container keys and subscribes them to the event dispatcher.
     */
    private function subscribe(...$containerKeys): void
    {
        $subscribers = array_merge(
            ...array_map($this->container->get(...), $containerKeys)
        );

        foreach ($subscribers as $subscriber) {
            $this->dispatcher->addSubscriber($subscriber);
        }
    }
}