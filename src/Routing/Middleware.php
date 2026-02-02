<?php declare(strict_types=1);

namespace App\Routing;

use Symfony\Component\Routing\Route;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class Middleware implements RouteDecoratorInterface
{
    public function __construct(
        private string $middleware,
        private int $priority = 0
    ) {
    }

    public function decorate(Route $route): void
    {
        $route->getDefault('_middleware')->insert($this->middleware, $this->priority);
    }
}
