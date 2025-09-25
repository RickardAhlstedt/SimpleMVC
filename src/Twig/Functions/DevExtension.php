<?php

namespace SimpleMVC\Twig\Functions;

use SimpleMVC\Templating\Twig\TwigFunctionInterface;
use SimpleMVC\Support\Debug;
use Twig\TwigFunction;

class DevExtension implements TwigFunctionInterface
{
    public function isStatic(): bool
    {
        return true;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('dump', 'SimpleMVC\\Twig\\Functions\\DevExtension::dump', ['is_safe' => ['html']]),
            new TwigFunction('dd', 'SimpleMVC\\Twig\\Functions\\DevExtension::dumpAndDie', ['is_safe' => ['html']]),
            new TwigFunction('debug_info', 'SimpleMVC\\Twig\\Functions\\DevExtension::debugInfo', ['is_safe' => ['html']]),
            new TwigFunction('memory_usage', 'SimpleMVC\\Twig\\Functions\\DevExtension::memoryUsage'),
            new TwigFunction('execution_time', 'SimpleMVC\\Twig\\Functions\\DevExtension::executionTime'),
            new TwigFunction('env', 'SimpleMVC\\Twig\\Functions\\DevExtension::getEnv'),
            new TwigFunction('config', 'SimpleMVC\\Twig\\Functions\\DevExtension::getConfig'),
            new TwigFunction('route_info', 'SimpleMVC\\Twig\\Functions\\DevExtension::routeInfo', ['is_safe' => ['html']]),
            new TwigFunction('debug_trace', 'SimpleMVC\\Twig\\Functions\\DevExtension::debugTrace', ['is_safe' => ['html']]),
            new TwigFunction('debug_memory', 'SimpleMVC\\Twig\\Functions\\DevExtension::debugMemory', ['is_safe' => ['html']]),
            new TwigFunction('debug_globals', 'SimpleMVC\\Twig\\Functions\\DevExtension::debugGlobals', ['is_safe' => ['html']]),
            new TwigFunction('debug_print', 'SimpleMVC\\Twig\\Functions\\DevExtension::debugPrint', ['is_safe' => ['html']]),
        ];
    }

    public static function dump($var, string $label = null): string
    {
        if (!Debug::isDev()) {
            return '';
        }

        // Use Debug class for consistency
        Debug::dump($var, $label);

        // Also return formatted HTML for immediate viewing in template
        return Debug::print($var, $label, true) ?? '';
    }

    public static function dumpAndDie($var, string $label = null): string
    {
        if (!Debug::isDev()) {
            return '';
        }

        // Use Debug::dd which handles both dumping and dying
        Debug::dd($var, $label);
        
        return ''; // This won't be reached due to die()
    }

    public static function debugInfo(): string
    {
        if (!Debug::isDev()) {
            return '';
        }

        $info = [
            'PHP Version' => PHP_VERSION,
            'Memory Usage' => self::formatBytes(memory_get_usage(true)),
            'Peak Memory' => self::formatBytes(memory_get_peak_usage(true)),
            'Execution Time' => self::executionTime() . 's',
            'Environment' => self::getEnvValue('APP_ENV') ?: 'unknown',
            'Debug Mode' => Debug::isDev() ? 'ON' : 'OFF',
            'Request Method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            'Request URI' => $_SERVER['REQUEST_URI'] ?? 'unknown',
        ];

        $html = '<div style="background: #1a1a1a; color: #f8f9fa; padding: 1rem; margin: 1rem 0; border-radius: 4px; font-family: monospace; font-size: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
        $html .= '<h4 style="margin: 0 0 0.5rem 0; color: #ffc107; border-bottom: 1px solid #ffc107; padding-bottom: 0.25rem;">🛠️ Debug Info</h4>';
        
        foreach ($info as $key => $value) {
            $html .= '<div style="margin: 0.25rem 0;"><span style="color: #6c757d;">' . htmlspecialchars($key) . ':</span> <span style="color: #28a745; font-weight: bold;">' . htmlspecialchars((string)$value) . '</span></div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }

    public static function memoryUsage(bool $formatted = true): string
    {
        $memory = Debug::memory('Twig Memory Check');
        return $formatted ? $memory['current_formatted'] : (string)$memory['current'];
    }

    public static function executionTime(): string
    {
        if (!defined('APP_START_TIME')) {
            return '0.000';
        }
        
        return number_format(microtime(true) - APP_START_TIME, 3);
    }

    public static function getEnv(string $key, $default = null)
    {
        return self::getEnvValue($key, $default);
    }

    public static function getConfig(string $key = null)
    {
        if (!Debug::isDev()) {
            return null;
        }

        try {
            $config = \SimpleMVC\Core\Config::getInstance();
            
            if ($key === null) {
                return $config->all();
            }
            
            return $config->get($key);
        } catch (\Exception $e) {
            Debug::log("Config error: " . $e->getMessage(), 'ERROR');
            return "Config error: " . $e->getMessage();
        }
    }

    public static function routeInfo(): string
    {
        if (!Debug::isDev()) {
            return '';
        }

        $info = [
            'URI' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'Method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            'Query String' => $_SERVER['QUERY_STRING'] ?? 'none',
            'Protocol' => $_SERVER['SERVER_PROTOCOL'] ?? 'unknown',
            'User Agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? 'unknown', 0, 100) . '...',
            'Remote IP' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'Request Time' => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'] ?? time()),
        ];

        $html = '<div style="background: #e3f2fd; border-left: 4px solid #2196f3; padding: 1rem; margin: 1rem 0; border-radius: 4px; font-family: monospace; font-size: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
        $html .= '<h4 style="margin: 0 0 0.5rem 0; color: #1976d2; border-bottom: 1px solid #2196f3; padding-bottom: 0.25rem;">🌐 Route Info</h4>';
        
        foreach ($info as $key => $value) {
            $html .= '<div style="margin: 0.25rem 0;"><span style="color: #666; font-weight: bold;">' . htmlspecialchars($key) . ':</span> <span style="color: #1976d2;">' . htmlspecialchars($value) . '</span></div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }

    public static function debugTrace(int $limit = 5): string
    {
        if (!Debug::isDev()) {
            return '';
        }

        $trace = Debug::trace($limit, 'Template Trace');
        
        $html = '<div style="background: #fff3e0; border-left: 4px solid #ff9800; padding: 1rem; margin: 1rem 0; border-radius: 4px; font-family: monospace; font-size: 11px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
        $html .= '<h4 style="margin: 0 0 0.5rem 0; color: #f57c00; border-bottom: 1px solid #ff9800; padding-bottom: 0.25rem;">📍 Stack Trace</h4>';
        
        foreach ($trace as $step) {
            $class = $step['class'] ? $step['class'] . $step['type'] : '';
            $html .= '<div style="margin: 0.25rem 0; padding: 0.25rem; background: rgba(255,152,0,0.1); border-radius: 2px;">';
            $html .= '<span style="color: #e65100; font-weight: bold;">' . $step['index'] . '.</span> ';
            $html .= '<span style="color: #f57c00;">' . $step['file'] . ':' . $step['line'] . '</span> ';
            $html .= '<span style="color: #ff9800;">' . $class . $step['function'] . '()</span>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }

    public static function debugMemory(string $label = 'Template Memory'): string
    {
        if (!Debug::isDev()) {
            return '';
        }

        $memory = Debug::memory($label);
        
        $html = '<div style="background: #e8f5e8; border-left: 4px solid #28a745; padding: 1rem; margin: 1rem 0; border-radius: 4px; font-family: monospace; font-size: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
        $html .= '<h4 style="margin: 0 0 0.5rem 0; color: #155724; border-bottom: 1px solid #28a745; padding-bottom: 0.25rem;">💾 Memory Usage</h4>';
        
        $html .= '<div style="margin: 0.25rem 0;"><span style="color: #6c757d;">Current:</span> <span style="color: #28a745; font-weight: bold;">' . $memory['current_formatted'] . '</span></div>';
        $html .= '<div style="margin: 0.25rem 0;"><span style="color: #6c757d;">Peak:</span> <span style="color: #dc3545; font-weight: bold;">' . $memory['peak_formatted'] . '</span></div>';
        
        $html .= '</div>';
        
        return $html;
    }

    public static function debugGlobals(): string
    {
        if (!Debug::isDev()) {
            return '';
        }

        Debug::globals(); // This will dump to the collector
        
        $html = '<div style="background: #ffeaa7; border-left: 4px solid #fdcb6e; padding: 1rem; margin: 1rem 0; border-radius: 4px; font-family: monospace; font-size: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
        $html .= '<h4 style="margin: 0 0 0.5rem 0; color: #e17055; border-bottom: 1px solid #fdcb6e; padding-bottom: 0.25rem;">🌍 Global Variables</h4>';
        $html .= '<div style="color: #6c757d;">Global variables have been dumped to the debug collector. Check the "Dumps" section in the debug toolbar.</div>';
        $html .= '</div>';
        
        return $html;
    }

    public static function debugPrint($variable, string $label = null): string
    {
        if (!Debug::isDev()) {
            return '';
        }

        return Debug::print($variable, $label, true) ?? '';
    }

    private static function getEnvValue(string $key, $default = null)
    {
        return getenv($key) ?: ($_ENV[$key] ?? $default);
    }

    private static function formatBytes(int $bytes): string
    {
        if ($bytes === 0) {
            return '0 B';
        }
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $base = log($bytes, 1024);
        return round(1024 ** ($base - floor($base)), 2) . ' ' . $units[floor($base)];
    }
}
