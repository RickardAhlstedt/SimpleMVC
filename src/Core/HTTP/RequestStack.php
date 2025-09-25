<?php

declare(strict_types=1);

namespace SimpleMVC\Core\HTTP;

class RequestStack
{
    private string $requestId = '';
    private array $get;
    private array $post;
    private array $server;
    private array $cookies;
    private array $files;
    private array $request;

    public function __construct()
    {
        $this->populateFromGlobals();
        $this->requestId = uniqid('', true);
    }

    public function getRequestId(): string
    {
        return $this->requestId;
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

    public function getUri(): string
    {
        return $this->server['REQUEST_URI'] ?? '/';
    }

    public function getPath(): string
    {
        $uri = $this->getUri();
        $queryPos = strpos($uri, '?');
        if ($queryPos !== false) {
            return substr($uri, 0, $queryPos);
        }
        return $uri;
    }

    public function getMethod(): string
    {
        return $this->server['REQUEST_METHOD'] ?? 'GET';
    }

    public function getQuery(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->get;
        }
        return $this->get[$key] ?? $default;
    }

    public function getPost(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->post;
        }
        return $this->post[$key] ?? $default;
    }

    public function getHeader(string $name): ?string
    {
        $headerKey = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $this->server[$headerKey] ?? null;
    }

    public function getSession(): Session
    {
        static $session = null;
        if ($session === null) {
            $session = new Session();
        }
        return $session;
    }

    public function isSecure(): bool
    {
        return isset($this->server['HTTPS']) && $this->server['HTTPS'] !== 'off';
    }

    public function getHost(): string
    {
        return $this->server['HTTP_HOST'] ?? 'localhost';
    }

    public function getScheme(): string
    {
        return $this->isSecure() ? 'https' : 'http';
    }

    public function getFullUrl(): string
    {
        return $this->getScheme() . '://' . $this->getHost() . $this->getUri();
    }

    public function isAjax(): bool
    {
        return $this->getHeader('X-Requested-With') === 'XMLHttpRequest';
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
