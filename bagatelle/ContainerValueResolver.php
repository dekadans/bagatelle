<?php

namespace tthe\Bagatelle;

use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class ContainerValueResolver implements ValueResolverInterface
{
    public function __construct(private ContainerInterface $container)
    {

    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($this->container->has($argument->getType())) {
            $dependency = $this->container->get($argument->getType());
            return [$dependency];
        }

        return [];
    }
}