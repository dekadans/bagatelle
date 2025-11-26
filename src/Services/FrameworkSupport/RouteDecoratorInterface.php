<?php

namespace App\Services\FrameworkSupport;

use Symfony\Component\Routing\Route;

interface RouteDecoratorInterface
{
    public function decorate(Route $route): void;
}
