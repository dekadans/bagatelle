<?php

namespace App\Services\Routing;

use Symfony\Component\Routing\Route;

interface RouteDecorator
{
    public function decorate(Route $route): void;
}