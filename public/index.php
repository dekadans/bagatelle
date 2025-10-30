<?php
/**
 * The main entrypoint for HTTP requests (front controller).
 * Uses Symfony HttpKernel and its event-based architecture.
 */

use App\Controllers\ErrorController;
use DI\Container;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
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
        $controller = $method->getName() === '__invoke'
            ? $class->getName()
            : $class->getName().'::'.$method->getName();
        $route->setDefault('_controller', $controller);
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
$dispatcher->addSubscriber(new ErrorListener(ErrorController::class));

// Set up HTTP kernel and execute request
$kernel = new HttpKernel($dispatcher, new ContainerControllerResolver($container));

$response = $kernel->handle($request);
$response->prepare($request);
$response->send();

$kernel->terminate($request, $response);