<?php

use App\Services\Core\ConsoleFactory;
use Psr\Container\ContainerInterface;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/environment.php';
/** @var ContainerInterface $container */
$container = require __DIR__ . '/../config/container.php';

$factory = new ConsoleFactory('Example Console App', $container);
$app = $factory->make();
$app->run();