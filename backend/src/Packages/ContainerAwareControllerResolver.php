<?php

namespace Packages;

use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;

class ContainerAwareControllerResolver extends ControllerResolver
{
    public function __construct(private ContainerInterface $container)
    {
        parent::__construct();
    }

    protected function instantiateController(string $class): object
    {
        // Usa o contêiner para obter a instância do controller
        return $this->container->get($class);
    }
}