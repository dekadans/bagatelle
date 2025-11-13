<?php
/*
 * Configures and returns a PSR-11 compliant dependency injection container.
 */

use App\Commands\GreetingCommand;
use App\Controllers\ErrorController;
use App\Services\Auth\AuthenticatableControllerLoader;
use App\Services\Auth\AuthenticationSubscriber;
use App\Services\GreetingInterface;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface as PsrEventDispatcherInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\PsrHttpMessage\ArgumentValueResolver\PsrServerRequestResolver;
use Symfony\Bridge\PsrHttpMessage\EventListener\PsrResponseListener;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\CommandLoader\ContainerCommandLoader;
use Symfony\Component\Console\EventListener\ErrorListener as ConsoleErrorListener;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\BackedEnumValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\EventListener\ErrorListener as HttpErrorListener;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Loader\AttributeDirectoryLoader;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as ContractsEventDispatcherInterface;
use Twig\Environment as Twig;
use Twig\Loader\FilesystemLoader as TwigFilesystemLoader;
use function DI\autowire;
use function DI\create;
use function DI\get;

$containerBuilder = new ContainerBuilder();

/*
 *
 * Application components.
 *
 */
$containerBuilder->addDefinitions([
    // HTTP request event subscribers.
    'app.http.subscribers' => [
        get(AuthenticationSubscriber::class),
    ],

    // Console commands. Add your command implementation classes here.
    'app.console.commands' => [
        // NOTE: Only add class names, not container references or instances.
        GreetingCommand::class
    ],

    // Console application event subscribers.
    'app.console.subscribers' => [
        // Add subscribers through container references, for example:
        // get(App\Events\SomeEventSubscriber:class)
    ],

    // Logging configuration
    // Factory method that returns a PSR-3 compliant logging implementation.
    'app.logger.default' => function (ContainerInterface $c) {
        $stream = $c->get('bagatelle.logger.stream'); // Normalized stream URI from LOG_STREAM env var
        $level = $c->get('bagatelle.logger.level'); // Log level from LOG_LEVEL env var (if available)
        return new Logger('default', [new StreamHandler($stream, $level)], [new PsrLogMessageProcessor()]);
    }
]);

/*
 *
 * Example service.
 * Feel free to remove this :)
 *
 */
$containerBuilder->addDefinitions([
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

/*
 *
 * Core dependencies.
 *
 */
$containerBuilder->addDefinitions([
    // Event Dispatcher
    EventDispatcherInterface::class => create(EventDispatcher::class),
    ContractsEventDispatcherInterface::class => get(EventDispatcherInterface::class),
    PsrEventDispatcherInterface::class => get(EventDispatcherInterface::class),

    // PSR-3 Logger
    'bagatelle.logger.stream' => function() {
        $envPath = !empty($_ENV['LOG_STREAM']) ? $_ENV['LOG_STREAM'] : 'var/log/default.log';
        if (str_contains($envPath, '://')) {
            return $envPath;
        }
        if ($envPath[0] !== '/') {
            $envPath = dirname(__DIR__).'/'.$envPath;
        }
        return 'file://' . $envPath;
    },
    'bagatelle.logger.level' => function () {
        return !empty($_ENV['LOG_LEVEL']) ? $_ENV['LOG_LEVEL'] : 'critical';
    },
    LoggerInterface::class => get('app.logger.default'),

    // PSR-7, PSR-17 and HttpFoundation bridge
    ServerRequestFactoryInterface::class => create(Psr17Factory::class),
    StreamFactoryInterface::class => create(Psr17Factory::class),
    UploadedFileFactoryInterface::class => create(Psr17Factory::class),
    ResponseFactoryInterface::class => create(Psr17Factory::class),
    UriFactoryInterface::class => create(Psr17Factory::class),
    HttpMessageFactoryInterface::class => create(PsrHttpFactory::class)
        ->constructor(
            get(ServerRequestFactoryInterface::class),
            get(StreamFactoryInterface::class),
            get(UploadedFileFactoryInterface::class),
            get(ResponseFactoryInterface::class)
        ),
    HttpFoundationFactoryInterface::class => autowire(HttpFoundationFactory::class),
    PsrResponseListener::class => create(PsrResponseListener::class)
        ->constructor(
            get(HttpFoundationFactoryInterface::class)
        ),

    // Templates
    Twig::class => function () {
        $cacheDir = __DIR__.'/../'.$_ENV['TWIG_CACHE_DIR'];
        $templateDir = __DIR__.'/../templates';
        $options = $_ENV['TWIG_CACHE'] ? ['cache' => $cacheDir] : [];
        return new Twig(new TwigFilesystemLoader($templateDir), $options);
    },

    // Routing
    FileLocatorInterface::class => function() {
        return new FileLocator(__DIR__.'/..');
    },
    RouterInterface::class => function (FileLocatorInterface $fileLocator) {
        $loader = new AttributeDirectoryLoader($fileLocator, new AuthenticatableControllerLoader());
        $cacheDirectory = __DIR__.'/../'.$_ENV['ROUTING_CACHE_DIR'];
        $options = $_ENV['ROUTING_CACHE'] ? ['cache_dir' => $cacheDirectory] : [];
        return new Router($loader, 'src/Controllers', $options);
    },
    UrlGeneratorInterface::class => get(RouterInterface::class),

    // HTTP kernel
    'bagatelle.http.subscribers' => [
        create(RouterListener::class)
            ->constructor(
                get(RouterInterface::class),
                create(RequestStack::class)
            ),
        create(HttpErrorListener::class)
            ->constructor(
                ErrorController::class,
                get(LoggerInterface::class)
            ),
        get(PsrResponseListener::class)
    ],
    ArgumentResolverInterface::class => function (ContainerInterface $c) {
        $enumResolver = $c->get(BackedEnumValueResolver::class);
        $psrRequestResolver = $c->get(PsrServerRequestResolver::class);
        $resolvers = array_merge(
            [$enumResolver, $psrRequestResolver],
            ArgumentResolver::getDefaultArgumentValueResolvers()
        );
        return new ArgumentResolver(argumentValueResolvers: $resolvers);
    },

    // Console
    CommandLoaderInterface::class => function (ContainerInterface $c) {
        $commandMap = [];
        foreach ($c->get('app.console.commands') as $commandClass) {
            $commandAttribute = new ReflectionClass($commandClass)->getAttributes(AsCommand::class);
            if ($commandAttribute) {
                $name = $commandAttribute[0]->newInstance()->name;
                $commandMap[$name] = $commandClass;
            }
        }
        return new ContainerCommandLoader($c, $commandMap);
    },
    'bagatelle.console.subscribers' => [
        create(ConsoleErrorListener::class)
            ->constructor(get(LoggerInterface::class))
    ]
]);

if ($_ENV['DI_CACHE']) {
    $containerBuilder->enableCompilation(__DIR__.'/../'.$_ENV['DI_CACHE_DIR']);
}

return $containerBuilder->build();