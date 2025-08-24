<?php

declare(strict_types=1);

namespace SimpleMVC\Middleware;

interface MiddlewareInterface
{
    /**
     * @param array $route The matched route array.
     * @return bool Return true to continue, false to stop (e.g. for auth failure).
     */
    public function handle(array $route): bool;
}