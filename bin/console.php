<?php

use App\Services\Core\ConsoleFactory;
use Psr\Container\ContainerInterface;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/environment.php';
/** @var ContainerInterface $container */
$container = require __DIR__ . '/../config/container.php';
/** @var class-string[] $commands */
$commands = require __DIR__ . '/../config/commands.php';

$factory = new ConsoleFactory('Example Console App', $container, $commands);
$app = $factory->make();
$app->run();