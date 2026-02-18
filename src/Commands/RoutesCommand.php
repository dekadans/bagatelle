<?php

namespace App\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\RouterInterface;

#[AsCommand('routes', 'Prints all registered routes.')]
class RoutesCommand extends Command
{
    public function __construct(private RouterInterface $router)
    {
        parent::__construct();
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Option(description: 'Filter by path prefix', shortcut: 'p')] ?string $prefix = null
    ): int {
        $routes = $this->getRoutes();

        if ($prefix) {
            $routes = array_filter($routes, fn ($r) => str_starts_with($r[1], $prefix));
        }

        if (!$routes) {
            $io->error('No routes were found!');
            return Command::FAILURE;
        }

        $io->table(
            ['Name', 'Path', 'Methods', 'Controller'],
            $routes
        );

        return Command::SUCCESS;
    }

    private function getRoutes(): array
    {
        $routes = [];
        foreach ($this->router->getRouteCollection()->all() as $name => $route) {
            $routes[] = [
                $name,
                $route->getPath(),
                implode(',', $route->getMethods()) ?: '*',
                $route->getDefault('_controller'),
            ];
        }

        usort($routes, fn ($a, $b) => $a[1] <=> $b[1]);

        return $routes;
    }
}