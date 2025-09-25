<?php

declare(strict_types=1);

namespace SimpleMVC\Core\HTTP;

class Response
{
    private int $statusCode;
    private array $headers;
    private string $body;

    public function __construct(string|Response $body = '', int $statusCode = 200, array $headers = [])
    {
        if ($body instanceof Response) {
            $this->body = $body->getContent();
        } else {
            $this->body = $body;
        }
        $this->statusCode = $statusCode;
        $this->headers = $headers;

        if (!isset($this->headers['Content-Type'])) {
           if (strpos($this->body, '<html') !== false || strpos($this->body, '<!DOCTYPE') !== false) {
                $this->headers['Content-Type'] = 'text/html; charset=utf-8';
            }
        }
    }

    public function send(): void
    {
        http_response_code($this->statusCode);
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
        echo $this->body;
    }

    public function setStatusCode(int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function addHeader(string $name, string $value): void
    {
        $this->headers[$name] = $value;
    }

    public function setHeader(string $name, string $value): void
    {
        $this->headers[$name] = $value;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getHeader(string $name): ?string
    {
        return $this->headers[$name] ?? null;
    }

    /** @deprecated */
    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    /** @deprecated */
    public function getBody(): string
    {
        return $this->body;
    }

    public function setContent(string $content): void
    {
        $this->body = $content;
    }

    public function getContent(): string
    {
        return $this->body;
    }
}
