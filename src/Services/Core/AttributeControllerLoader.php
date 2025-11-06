<?php

namespace App\Services\Core;

use App\Services\Auth\RequiresAuth;
use Symfony\Component\Routing\Loader\AttributeClassLoader;
use Symfony\Component\Routing\Route;

class AttributeControllerLoader extends AttributeClassLoader
{
    protected function configureRoute(Route $route, \ReflectionClass $class, \ReflectionMethod $method, object $attr): void
    {
        $route->setDefault('_controller', $this->getControllerName($class, $method));
        $route->setDefault(RequiresAuth::REQUEST_ATTRIBUTE, $this->isProtected($class, $method));
    }

    private function getControllerName(\ReflectionClass $class, \ReflectionMethod $method): string
    {
        if ($method->getName() === '__invoke') {
            return $class->getName();
        } else {
            return $class->getName() . '::' . $method->getName();
        }
    }

    private function isProtected(\ReflectionClass $class, \ReflectionMethod $method): bool
    {
        return (bool) ($method->getAttributes(RequiresAuth::class) ?: $class->getAttributes(RequiresAuth::class));
    }
}