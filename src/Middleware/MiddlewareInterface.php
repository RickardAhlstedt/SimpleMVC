<?php

declare(strict_types=1);

namespace SimpleMVC\Middleware;

use SimpleMVC\Core\HTTP\RequestStack;
use SimpleMVC\Core\HTTP\Response;

interface MiddlewareInterface
{
    /**
     * Process the request through middleware.
     * 
     * @param RequestStack $request The request object
     * @param callable $next The next middleware in the chain
     * @return Response The response object
     */
    public function process(RequestStack $request, callable $next): Response;
}
