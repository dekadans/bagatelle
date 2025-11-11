<?php

use App\Services\ApplicationFactory;
use Psr\Container\ContainerInterface;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/environment.php';
/** @var ContainerInterface $container */
$container = require __DIR__ . '/../config/container.php';

$factory = new ApplicationFactory($container);
$app = $factory->makeConsole('Example Console App');
$app->run();