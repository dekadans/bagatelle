<?php

namespace App\Services\Auth;

use App\Services\FrameworkSupport\RouteDecoratorInterface;
use Symfony\Component\Routing\Route;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class Auth implements RouteDecoratorInterface
{
    public function decorate(Route $route): void
    {
        $route->setDefault(
            '_route_subscribers',
            array_merge(
                ($route->getDefault('_route_subscribers') ?? []),
                [AuthenticationSubscriber::class]
            )
        );
    }
}
