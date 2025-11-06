<?php
/**
 * The main entrypoint for HTTP requests (front controller).
 * Uses Symfony HttpKernel and its event-based architecture.
 */

use App\Controllers\ErrorController;
use DI\Container;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\PsrHttpMessage\ArgumentValueResolver\PsrServerRequestResolver;
use Symfony\Bridge\PsrHttpMessage\EventListener\PsrResponseListener;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\BackedEnumValueResolver;
use Symfony\Component\HttpKernel\Controller\ContainerControllerResolver;
use Symfony\Component\HttpKernel\EventListener\ErrorListener;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Routing\Router;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/environment.php';

// Setup DI container and grab what we need
/** @var Container $container */
$container = require __DIR__ . '/../config/container.php';
/** @var EventDispatcherInterface $dispatcher */
$dispatcher = $container->get(EventDispatcherInterface::class);
/** @var LoggerInterface $log */
$log = $container->get(LoggerInterface::class);
/** @var Router $router */
$router = $container->get(Router::class);

// Listen to kernel events
$dispatcher->addSubscriber(new RouterListener($router, new RequestStack()));
$dispatcher->addSubscriber(new ErrorListener(ErrorController::class, $log));
$dispatcher->addSubscriber(new PsrResponseListener());

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

$request = Request::createFromGlobals();

$response = $kernel->handle($request);
$response->prepare($request);
$response->send();

$kernel->terminate($request, $response);