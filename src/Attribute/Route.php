<?php

declare(strict_types=1);

namespace SimpleMVC\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Route
{
    public string $name;
    public string $path;
    public string $method;
    public array $options;
    public string $middleware;

    public function __construct(string $name, string $path, string $method = 'GET', array $options = [], string $middleware = "")
    {
        $this->name = $name;
        $this->path = $path;
        $this->method = strtoupper($method);
        $this->options = $options;
        $this->middleware = $middleware;
    }
}
