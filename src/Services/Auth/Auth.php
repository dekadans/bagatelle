<?php

namespace App\Services\Auth;

use App\Services\Routing\RouteDecorator;
use Symfony\Component\Routing\Route;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class Auth implements RouteDecorator
{
    public function decorate(Route $route): void
    {
        $route->setDefault(
            '_subscribers',
            array_merge(
                ($route->getDefault('_subscribers') ?? []),
                [AuthenticationSubscriber::class]
            )
        );
    }
}