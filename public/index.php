<?php
/**
 * The main entrypoint for HTTP requests (front controller).
 * Uses Symfony HttpKernel and its event-based architecture.
 */

use App\Controllers\ErrorController;
use App\Services\Auth\RequiresAuth;
use DI\Container;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\PsrHttpMessage\ArgumentValueResolver\PsrServerRequestResolver;
use Symfony\Bridge\PsrHttpMessage\EventListener\PsrResponseListener;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\BackedEnumValueResolver;
use Symfony\Component\HttpKernel\Controller\ContainerControllerResolver;
use Symfony\Component\HttpKernel\EventListener\ErrorListener;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Loader\AttributeClassLoader;
use Symfony\Component\Routing\Loader\AttributeDirectoryLoader;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/environment.php';

// Setup DI container and grab what we need
/** @var Container $container */
$container = require __DIR__ . '/../config/container.php';
/** @var FileLocatorInterface $fileLocator */
$fileLocator = $container->get(FileLocatorInterface::class);
/** @var EventDispatcherInterface $dispatcher */
$dispatcher = $container->get(EventDispatcherInterface::class);
/** @var LoggerInterface $log */
$log = $container->get(LoggerInterface::class);

// Create HTTP request object
$request = Request::createFromGlobals();

// Setup routing
$attributeControllerLoader = new class() extends AttributeClassLoader {
    protected function configureRoute(
        Route $route,
        \ReflectionClass $class,
        \ReflectionMethod $method,
        object $attr
    ): void {
        // Set the controller class/method that should be called.
        $controller = $method->getName() === '__invoke'
            ? $class->getName()
            : $class->getName().'::'.$method->getName();
        $route->setDefault('_controller', $controller);

        // Set authentication if class or method has attribute.
        $requiresAuth = count($class->getAttributes(RequiresAuth::class))
            || count($method->getAttributes(RequiresAuth::class));
        $route->setDefault('_auth', $requiresAuth);
    }
};

$routeDirLoader = new AttributeDirectoryLoader($fileLocator, $attributeControllerLoader);
$routes = $routeDirLoader->load('src/Controllers');
$requestContext = (new RequestContext())->fromRequest($request);
$matcher = new UrlMatcher($routes, $requestContext);
$generator = new UrlGenerator($routes, $requestContext);

// Add the URL generator to the container, making it injectable in controllers and services.
$container->set(UrlGeneratorInterface::class, $generator);

// Listen to kernel events
$dispatcher->addSubscriber(new RouterListener($matcher, new RequestStack()));
$dispatcher->addSubscriber(new ErrorListener(ErrorController::class, $log));
$dispatcher->addSubscriber($container->get(PsrResponseListener::class));

// Add custom event subscribers
foreach (require __DIR__ . '/../config/subscribers.php' as $subscriber) {
    $dispatcher->addSubscriber($container->get($subscriber));
}

$controllerResolver = new ContainerControllerResolver($container);
$argumentValueResolvers = array_merge(
    [new BackedEnumValueResolver(), $container->get(PsrServerRequestResolver::class)],
    ArgumentResolver::getDefaultArgumentValueResolvers()
);
$argumentResolver = new ArgumentResolver(null, $argumentValueResolvers);

// Set up HTTP kernel and execute request
$kernel = new HttpKernel($dispatcher, $controllerResolver, new RequestStack(), $argumentResolver);

$response = $kernel->handle($request);
$response->prepare($request);
$response->send();

$kernel->terminate($request, $response);