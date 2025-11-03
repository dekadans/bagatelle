<?php

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\CommandLoader\ContainerCommandLoader;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/environment.php';

/** @var ContainerInterface $container */
$container = require __DIR__ . '/../config/container.php';
/** @var EventDispatcherInterface $dispatcher */
$dispatcher = $container->get(EventDispatcherInterface::class);

$commands = require __DIR__ . '/../config/commands.php';

$consoleApp = new Application('Example Console App');
$consoleApp->setCommandLoader(new ContainerCommandLoader($container, $commands));
$consoleApp->setDispatcher($dispatcher);

$consoleApp->run();