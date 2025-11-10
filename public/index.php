<?php
/**
 * The main entrypoint for HTTP requests (front controller).
 * Uses Symfony HttpKernel and its event-based architecture.
 */

use App\Services\Core\KernelFactory;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/environment.php';

/** @var ContainerInterface $container */
$container = require __DIR__ . '/../config/container.php';

$factory = new KernelFactory($container);
$kernel = $factory->make();

$request = Request::createFromGlobals();

$response = $kernel->handle($request);
$response->prepare($request);
$response->send();

$kernel->terminate($request, $response);