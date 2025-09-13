<?php

namespace SimpleMVC\Routing;

use SimpleMVC\Core\Container;

class RouteParamResolverRegistry
{

    /** @var RouteParamResolverInterface[] */
    private array $resolvers = [];

    public function addResolver(RouteParamResolverInterface $resolver): void
    {
        $this->resolvers[] = $resolver;
    }

    public function resolve(string $paramName, string $value, \ReflectionParameter $reflectionParameter, Container $container): mixed
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->supports($paramName, $reflectionParameter)) {
                return $resolver->resolve($value, $reflectionParameter, $container);
            }
        }
        return $value;
    }

}
