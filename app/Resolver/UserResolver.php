<?php

namespace App\Resolver;

use App\Entities\User;
use ReflectionParameter;
use SimpleMVC\Core\Container;
use SimpleMVC\Routing\RouteParamResolverInterface;

class UserResolver implements RouteParamResolverInterface
{

    public function supports(string $paramName, ReflectionParameter $reflectionParameter): bool
    {
        return $reflectionParameter->getType()
            && $reflectionParameter->getType()->getName() === User::class;
    }

    public function resolve(string $value, ReflectionParameter $reflectionParameter, Container $container): mixed
    {
        $em = $container->get(\SimpleMVC\Database\EntityManager::class);
        return $em->getRepository(User::class)->find($value);
    }
}
