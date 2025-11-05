<?php

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\CommandLoader\ContainerCommandLoader;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/environment.php';

/** @var ContainerInterface $container */
$container = require __DIR__ . '/../config/container.php';
/** @var EventDispatcherInterface $dispatcher */
$dispatcher = $container->get(EventDispatcherInterface::class);

$commandMap = [];
foreach (require __DIR__ . '/../config/commands.php' as $commandClass) {
    $commandAttribute = (new ReflectionClass($commandClass))->getAttributes(AsCommand::class);
    if (!$commandAttribute) {
        die('Console commands must be defined using the AsCommand attribute, please revise ' . $commandClass . PHP_EOL);
    }

    $name = $commandAttribute[0]->newInstance()->name;
    $commandMap[$name] = $commandClass;
}

$consoleApp = new Application('Example Console App');
$consoleApp->setCommandLoader(new ContainerCommandLoader($container, $commandMap));
$consoleApp->setDispatcher($dispatcher);

$consoleApp->run();