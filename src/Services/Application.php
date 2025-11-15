<?php

namespace App\Services;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
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
    private LoggerInterface $logger;

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

        $this->container = require __DIR__ . '/../../config/container.php';
        $this->dispatcher = $this->container->get(EventDispatcherInterface::class);
        $this->logger = $this->container->get(LoggerInterface::class);

        date_default_timezone_set($_ENV['TIMEZONE']);
        error_reporting(E_ALL);

        set_error_handler(function(int $level, string $message, string $file, int $line) {
            $this->logger->warning("PHP Notice: $message", [
                'level' => $level,
                'file' => $file,
                'line' => $line
            ]);
        });

        // HttpKernel and Console generally catch and report all exceptions and errors.
        // This is for anything happening before or after the main application processing.
        set_exception_handler(function(\Throwable $ex) {
            $this->logger->emergency(
                "Unhandled exception: {$ex->getMessage()}",
                ['exception' => $ex]
            );
        });
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

        $kernel = new HttpKernel(
            $this->dispatcher,
            $controllerResolver,
            new RequestStack(),
            $argumentResolver,
            true
        );

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