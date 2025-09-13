<?php

declare(strict_types=1);

namespace SimpleMVC\Core\HTTP;

class RequestStack
{
    private array $get;
    private array $post;
    private array $server;
    private array $cookies;
    private array $files;
    private array $request;

    public function __construct()
    {
        $this->populateFromGlobals();
    }

    public function populateFromGlobals(): void
    {
        $this->get = $_GET ?? [];
        $this->post = $_POST ?? [];
        $this->server = $_SERVER ?? [];
        $this->cookies = $_COOKIE ?? [];
        $this->files = $_FILES ?? [];
        $this->request = array_merge($this->get, $this->post);
    }

    public function getQueryParams(): array
    {
        return $this->get;
    }

    public function getPostParams(): array
    {
        return $this->post;
    }

    public function getServerParams(): array
    {
        return $this->server;
    }

    public function getCookies(): array
    {
        return $this->cookies;
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    public function getRequestParams(): array
    {
        return $this->request;
    }

    public function get(string $key, $default = null)
    {
        return $this->request[$key] ?? $default;
    }
}
