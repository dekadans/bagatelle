<?php declare(strict_types=1);

/*
 * Configures and returns a PSR-11 compliant dependency injection container.
 *
 * Uses PHP-DI by default: https://php-di.org/
 */

use App\Commands\GreetingCommand;
use App\Services\Auth\AuthenticationSubscriber;
use App\Services\GreetingInterface;
use DI\ContainerBuilder;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use function DI\autowire;

$containerBuilder = new ContainerBuilder();

/*
 *
 * Application configuration.
 *
 */
$containerBuilder->addDefinitions([
    // Default application timezone.
    // Set to one from https://www.php.net/manual/en/timezones.php
    'app.timezone' => 'UTC',

    // --- HTTP Application Configuration

    // HTTP request event subscribers.
    'app.http.subscribers' => [
        autowire(AuthenticationSubscriber::class),
    ],

    // PSR-3 logger implementation for HTTP application.
    'app.http.logger' => function (StreamHandler $handler) {
        // StreamHandler comes configured values from .env variables LOG_STREAM and LOG_LEVEL.
        $handler->setFormatter(new JsonFormatter());
        return new Logger('bagatelle-http', [$handler], [new PsrLogMessageProcessor()]);
    },

    // --- Console Application Configuration

    // The name of the console application.
    'app.console.name' => 'Example Console Application',

    // Console commands. Add your command implementation classes here.
    'app.console.commands' => [
        // NOTE: Only add class names, not container references or instances.
        GreetingCommand::class
    ],

    // Console application event subscribers.
    'app.console.subscribers' => [
        // Add subscribers through container references, for example:
        // autowire(App\Events\SomeEventSubscriber:class)
    ],

    // PSR-3 logger implementation for console application.
    'app.console.logger' => function (ConsoleHandler $handler) {
        return new Logger('bagatelle-cli', [$handler], [new PsrLogMessageProcessor()]);
    }
]);

/*
 *
 * Controllers & Commands.
 *
 */
$containerBuilder->addDefinitions([
    // Adding autowire definitions here is not necessary to make it work,
    // however, it will improve performance when using a compiled container in production.

    \App\Controllers\IndexController::class => autowire(),
    \App\Controllers\ErrorController::class => autowire(),

    GreetingCommand::class => autowire()
]);

/*
 *
 * Services.
 *
 */
$containerBuilder->addDefinitions([
    // The bundled dependency injection container autowires dependencies when possible, but here you can explicitly
    // define your services when needed, like when there's dependencies on interfaces. For example:
    // App\Services\EncabulationInterface::class => autowire(App\Services\TurboEncabulator::class)

    // Service used in the default Bagatelle welcome page and sample console command.
    GreetingInterface::class => function () {
        return new class () implements GreetingInterface
        {
            public function greet(): string
            {
                $greetings = [
                    'Hello!', 'Hi!', 'Hey!', 'Yo!', 'Hiya!',
                    "How's everything?", 'How are you?', "How's it going?", "What's up?", 'Howdy!',
                    'Greetings!', 'Welcome!', 'Nice to see you!', 'Long time no see!', 'How have you been?',
                    'Good to see you!', 'Pleased to meet you!', 'How do you do?', 'Hey there!', "What's new?"
                ];
                return $greetings[array_rand($greetings)];
            }
        };
    }
]);

// Add the core Bagatelle configuration.
$containerBuilder->addDefinitions(__DIR__ . '/core.php');

// If configured, we set the container to compile down to set instructions.
if (!empty($_ENV['DI_CACHE_DIR'])) {
    $containerBuilder->enableCompilation(__DIR__ . '/../' . $_ENV['DI_CACHE_DIR']);
}

// This file can return any object implementing the PSR-11 ContainerInterface,
// it doesn't have to be the bundled PHP-DI.
return $containerBuilder->build();
