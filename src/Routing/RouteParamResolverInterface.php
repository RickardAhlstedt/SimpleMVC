<?php

namespace SimpleMVC\Routing;

use ReflectionParameter;
use SimpleMVC\Core\Container;

interface RouteParamResolverInterface
{
    public function supports(string $paramName, ReflectionParameter $reflectionParameter): bool;

    public function resolve(string $value, ReflectionParameter $reflectionParameter, Container $container): mixed;

}
