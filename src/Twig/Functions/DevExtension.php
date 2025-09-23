<?php

namespace SimpleMVC\Twig\Functions;

use SimpleMVC\Templating\Twig\TwigFunctionInterface;
use Twig\TwigFunction;

class DevExtension implements TwigFunctionInterface
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('dump', [$this, 'dump'], ['is_safe' => ['html']]),
            new TwigFunction('dd', [$this, 'dumpAndDie'], ['is_safe' => ['html']]),
            new TwigFunction('debug_info', [$this, 'debugInfo'], ['is_safe' => ['html']]),
            new TwigFunction('memory_usage', [$this, 'memoryUsage']),
            new TwigFunction('execution_time', [$this, 'executionTime']),
            new TwigFunction('env', [$this, 'getEnv']),
            new TwigFunction('config', [$this, 'getConfig']),
            new TwigFunction('route_info', [$this, 'routeInfo'], ['is_safe' => ['html']]),
            new TwigFunction('sql_queries', [$this, 'getSqlQueries'], ['is_safe' => ['html']]),
        ];
    }

    public function dump($var): string
    {
        if (!$this->isDevMode()) {
            return '';
        }

        ob_start();
        echo '<pre style="background: #f8f9fa; border: 1px solid #dee2e6; padding: 1rem; margin: 1rem 0; border-radius: 4px; overflow-x: auto;">';
        var_dump($var);
        echo '</pre>';
        return ob_get_clean();
    }

    public function dumpAndDie($var): void
    {
        if (!$this->isDevMode()) {
            return;
        }

        echo $this->dump($var);
        die();
    }

    public function debugInfo(): string
    {
        if (!$this->isDevMode()) {
            return '';
        }

        $info = [
            'PHP Version' => PHP_VERSION,
            'Memory Usage' => $this->formatBytes(memory_get_usage(true)),
            'Peak Memory' => $this->formatBytes(memory_get_peak_usage(true)),
            'Execution Time' => $this->executionTime() . 's',
            'Environment' => $this->getEnv('APP_ENV') ?: 'unknown',
            'Debug Mode' => $this->isDevMode() ? 'ON' : 'OFF',
        ];

        $html = '<div style="background: #1a1a1a; color: #f8f9fa; padding: 1rem; margin: 1rem 0; border-radius: 4px; font-family: monospace; font-size: 12px;">';
        $html .= '<h4 style="margin: 0 0 0.5rem 0; color: #ffc107;">🛠️ Debug Info</h4>';
        
        foreach ($info as $key => $value) {
            $html .= '<div><span style="color: #6c757d;">' . htmlspecialchars($key) . ':</span> ' . htmlspecialchars($value) . '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }

    public function memoryUsage(bool $formatted = true): string
    {
        $usage = memory_get_usage(true);
        return $formatted ? $this->formatBytes($usage) : (string)$usage;
    }

    public function executionTime(): string
    {
        if (!defined('APP_START_TIME')) {
            return '0.000';
        }
        
        return number_format(microtime(true) - APP_START_TIME, 3);
    }

    public function getEnv(string $key, $default = null)
    {
        return getenv($key) ?: ($_ENV[$key] ?? $default);
    }

    public function getConfig(string $key = null)
    {
        if (!$this->isDevMode()) {
            return null;
        }

        try {
            $config = \SimpleMVC\Core\Config::getInstance();
            
            if ($key === null) {
                return $config->all();
            }
            
            return $config->get($key);
        } catch (\Exception $e) {
            return "Config error: " . $e->getMessage();
        }
    }

    public function routeInfo(): string
    {
        if (!$this->isDevMode()) {
            return '';
        }

        $info = [
            'URI' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'Method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            'Query String' => $_SERVER['QUERY_STRING'] ?? '',
            'User Agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'Remote IP' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        ];

        $html = '<div style="background: #e3f2fd; border: 1px solid #2196f3; padding: 1rem; margin: 1rem 0; border-radius: 4px; font-family: monospace; font-size: 12px;">';
        $html .= '<h4 style="margin: 0 0 0.5rem 0; color: #1976d2;">🌐 Route Info</h4>';
        
        foreach ($info as $key => $value) {
            $html .= '<div><span style="color: #666;">' . htmlspecialchars($key) . ':</span> ' . htmlspecialchars($value) . '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }

    public function getSqlQueries(): string
    {
        if (!$this->isDevMode()) {
            return '';
        }

        // This would require implementing query logging in your DatabaseInterface
        // For now, return a placeholder
        $html = '<div style="background: #fff3e0; border: 1px solid #ff9800; padding: 1rem; margin: 1rem 0; border-radius: 4px; font-family: monospace; font-size: 12px;">';
        $html .= '<h4 style="margin: 0 0 0.5rem 0; color: #f57c00;">🗄️ SQL Queries</h4>';
        $html .= '<div style="color: #666;">Query logging not implemented yet</div>';
        $html .= '</div>';
        
        return $html;
    }

    private function isDevMode(): bool
    {
        return ($this->getEnv('APP_ENV') === 'dev') || ($this->getEnv('APP_DEBUG') === 'true');
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $base = log($bytes, 1024);
        return round(1024 ** ($base - floor($base)), 2) . ' ' . $units[floor($base)];
    }
}
