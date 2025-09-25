<?php

namespace SimpleMVC\Debug\Collector;

use SimpleMVC\Debug\DataCollectorInterface;

class RequestCollector implements DataCollectorInterface
{
    public function getName(): string
    {
        return 'request';
    }

    public function getIcon(): string
    {
        return '🌐';
    }

    public function getLabel(): string
    {
        return 'Request';
    }

    public function getPriority(): int
    {
        return 100;
    }

    public function collect(): array
    {
        return [
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'query' => $_GET,
            'post' => $_POST,
            'headers' => $this->getHeaders(),
            'cookies' => $_COOKIE,
            'session' => $_SESSION ?? [],
            'server' => $this->filterServerVars($_SERVER),
        ];
    }

    private function getHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace('_', '-', substr($key, 5));
                $headers[ucwords(strtolower($header), '-')] = $value;
            }
        }
        return $headers;
    }

    private function filterServerVars(array $server): array
    {
        $filtered = [];
        $allowed = ['SERVER_NAME', 'SERVER_PORT', 'HTTPS', 'REMOTE_ADDR', 'USER_AGENT'];
        
        foreach ($allowed as $key) {
            if (isset($server[$key])) {
                $filtered[$key] = $server[$key];
            }
        }
        
        return $filtered;
    }
}