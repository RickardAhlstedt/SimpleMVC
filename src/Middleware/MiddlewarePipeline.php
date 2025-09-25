<?php

namespace SimpleMVC\Middleware;

use SimpleMVC\Core\HTTP\RequestStack;
use SimpleMVC\Core\HTTP\Response;

class MiddlewarePipeline
{
    private array $middleware = [];

    public function addMiddleware(MiddlewareInterface $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    public function handle(RequestStack $request, callable $finalHandler): Response
    {
        $pipeline = array_reduce(
            array_reverse($this->middleware),
            function (callable $next, MiddlewareInterface $middleware) {
                return function (RequestStack $request) use ($middleware, $next): Response {
                    return $middleware->process($request, $next);
                };
            },
            $finalHandler
        );

        return $pipeline($request);
    }
}