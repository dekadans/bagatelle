<?php

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
use Psr\Container\ContainerInterface;
use function DI\autowire;

$containerBuilder = new ContainerBuilder();

/*
 *
 * Application configuration.
 *
 */
$containerBuilder->addDefinitions([
    // HTTP request event subscribers.
    'app.http.subscribers' => [
        autowire(AuthenticationSubscriber::class),
    ],

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

    // Logging configuration
    // Factory method that returns a PSR-3 compliant logging implementation.
    'app.logger.default' => function (ContainerInterface $c) {
        $stream = $c->get('bagatelle.logger.stream'); // Normalized stream URI from LOG_STREAM env var
        $level = $c->get('bagatelle.logger.level'); // Log level from LOG_LEVEL env var (if available)
        $handler = new StreamHandler($stream, $level)->setFormatter(new JsonFormatter());
        return new Logger('bagatelle', [$handler], [new PsrLogMessageProcessor()]);
    }
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
