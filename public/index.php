<?php
/**
 * The main entrypoint for HTTP requests (front controller).
 * Uses Symfony HttpKernel and its event-based architecture.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Psr\Container\ContainerInterface;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ContainerControllerResolver;
use Symfony\Component\HttpKernel\EventListener\ErrorListener;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Routing\Loader\AttributeDirectoryLoader;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use tthe\Bagatelle\AttributeRouteControllerLoader;
use tthe\Bagatelle\ContainerValueResolver;
use tthe\Bagatelle\ErrorHandler;

$envFile = __DIR__ . '/../.env';

if (!file_exists($envFile)) {
    die('Error! No .env exists. Create a copy from .env.example.');
}

$dotenv = new Dotenv();
$dotenv->load($envFile);

if ($_ENV["BAGATELLE_DETAILED_ERRORS"]) {
    error_reporting(E_ALL & ~E_NOTICE);
} else {
    error_reporting(0);
}

// Setup DI container and grab what we need
/** @var ContainerInterface $container */
$container = require __DIR__ . '/../config/container.php';
/** @var FileLocatorInterface $fileLocator */
$fileLocator = $container->get(FileLocatorInterface::class);
/** @var EventDispatcherInterface $dispatcher */
$dispatcher = $container->get(EventDispatcherInterface::class);

// Create HTTP request
$request = Request::createFromGlobals();

// Setup routing
$routeDirLoader = new AttributeDirectoryLoader($fileLocator, new AttributeRouteControllerLoader());
$routes = $routeDirLoader->load('src/Controllers');
$matcher = new UrlMatcher($routes, new RequestContext());

// Listen to kernel events
$dispatcher->addSubscriber(new RouterListener($matcher, new RequestStack()));
$dispatcher->addSubscriber(new ErrorListener(ErrorHandler::class));

// Resolvers for controller classes and action parameters
$controllerResolver = new ContainerControllerResolver($container);
$containerResolver = new ContainerValueResolver($container);
$resolvers = array_merge(ArgumentResolver::getDefaultArgumentValueResolvers(), [$containerResolver]);
$argumentResolver = new ArgumentResolver(argumentValueResolvers: $resolvers);

// Set up HTTP kernel and execute request
$kernel = new HttpKernel(
    $dispatcher,
    $controllerResolver,
    new RequestStack(),
    $argumentResolver
);

$response = $kernel->handle($request);
$response->prepare($request);
$response->send();

$kernel->terminate($request, $response);