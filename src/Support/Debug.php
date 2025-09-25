<?php

namespace SimpleMVC\Support;

use SimpleMVC\Debug\Collector\DumpCollector;

class Debug
{
    /**
     * Dump a variable to the debug collector
     */
    public static function dump($variable, string $label = null): void
    {
        if (class_exists(DumpCollector::class)) {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
            DumpCollector::dump($variable, $label, $backtrace);
        }
    }

    /**
     * Dump and die - outputs the variable and stops execution
     */
    public static function dd($variable, string $label = null): void
    {
        self::dump($variable, $label);
        
        // Also output to browser for immediate viewing
        if (php_sapi_name() !== 'cli') {
            echo '<pre style="background: #1a1a1a; color: #f8f9fa; padding: 1rem; margin: 1rem; border-radius: 4px; font-family: monospace; font-size: 12px; border-left: 4px solid #dc3545;">';
            echo '<strong style="color: #dc3545;">🛑 Debug::dd() - Execution stopped</strong>' . PHP_EOL;
            if ($label) {
                echo '<strong style="color: #ffc107;">Label: ' . htmlspecialchars($label) . '</strong>' . PHP_EOL . PHP_EOL;
            }
            var_dump($variable);
            echo '</pre>';
        } else {
            // CLI output
            echo "🛑 Debug::dd() - Execution stopped" . PHP_EOL;
            if ($label) {
                echo "Label: {$label}" . PHP_EOL . PHP_EOL;
            }
            var_dump($variable);
        }
        
        die(1);
    }

    /**
     * Log a message with timestamp and backtrace info
     */
    public static function log(string $message, string $level = 'INFO'): void
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller = $backtrace[1] ?? $backtrace[0];
        
        $timestamp = date('Y-m-d H:i:s');
        $file = basename($caller['file'] ?? 'unknown');
        $line = $caller['line'] ?? '?';
        $function = $caller['function'] ?? 'unknown';
        
        $logMessage = "[{$timestamp}] [{$level}] {$message} (in {$file}:{$line} {$function}())";
        
        error_log($logMessage);
        
        // Also add to debug collector if available
        self::dump([
            'level' => $level,
            'message' => $message,
            'timestamp' => $timestamp,
            'file' => $file,
            'line' => $line,
            'function' => $function,
        ], "Debug Log: {$level}");
    }

    /**
     * Measure execution time of a callable
     */
    public static function time(callable $callback, string $label = 'Execution'): mixed
    {
        $start = microtime(true);
        $result = $callback();
        $end = microtime(true);
        
        $executionTime = ($end - $start) * 1000; // Convert to milliseconds
        
        self::log("⏱️  {$label}: {$executionTime}ms", 'TIMING');
        
        return $result;
    }

    /**
     * Memory usage snapshot
     */
    public static function memory(string $label = 'Memory Check'): array
    {
        $memory = [
            'current' => memory_get_usage(true),
            'peak' => memory_get_peak_usage(true),
            'current_formatted' => self::formatBytes(memory_get_usage(true)),
            'peak_formatted' => self::formatBytes(memory_get_peak_usage(true)),
            'timestamp' => microtime(true),
            'label' => $label,
        ];
        
        self::dump($memory, "Memory: {$label}");
        
        return $memory;
    }

    /**
     * Get current call stack
     */
    public static function trace(int $limit = 10, string $label = 'Stack Trace'): array
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $limit + 1);
        array_shift($backtrace); // Remove this method from the trace
        
        $formattedTrace = [];
        foreach ($backtrace as $i => $trace) {
            $formattedTrace[] = [
                'index' => $i + 1,
                'file' => basename($trace['file'] ?? 'unknown'),
                'line' => $trace['line'] ?? '?',
                'function' => $trace['function'] ?? 'unknown',
                'class' => $trace['class'] ?? null,
                'type' => $trace['type'] ?? null,
            ];
        }
        
        self::dump($formattedTrace, $label);
        
        return $formattedTrace;
    }

    /**
     * Dump all globals ($_GET, $_POST, $_SESSION, etc.)
     */
    public static function globals(): void
    {
        $globals = [
            '_GET' => $_GET,
            '_POST' => $_POST,
            '_COOKIE' => $_COOKIE,
            '_SERVER' => self::cleanServerArray($_SERVER),
            '_SESSION' => $_SESSION ?? [],
            '_ENV' => array_slice($_ENV, 0, 20, true), // Limit env vars
        ];
        
        self::dump($globals, 'Global Variables');
    }

    /**
     * Quick var_dump with better formatting
     */
    public static function print($variable, string $label = null, bool $return = false): ?string
    {
        ob_start();
        
        if (php_sapi_name() !== 'cli') {
            echo '<pre style="background: #f8f9fa; border: 1px solid #dee2e6; padding: 1rem; margin: 1rem 0; border-radius: 4px; font-size: 12px; border-left: 4px solid #007bff;">';
            if ($label) {
                echo '<strong style="color: #007bff;">' . htmlspecialchars($label) . ':</strong>' . PHP_EOL . PHP_EOL;
            }
            var_dump($variable);
            echo '</pre>';
        } else {
            if ($label) {
                echo $label . ':' . PHP_EOL . PHP_EOL;
            }
            var_dump($variable);
            echo PHP_EOL;
        }
        
        $output = ob_get_clean();
        
        if ($return) {
            return $output;
        }
        
        echo $output;
        return null;
    }

    /**
     * Create a performance benchmark
     */
    public static function benchmark(string $name): object
    {
        return new class($name) {
            private float $start;
            private string $name;
            private array $laps = [];
            
            public function __construct(string $name)
            {
                $this->name = $name;
                $this->start = microtime(true);
                Debug::log("🏁 Started benchmark: {$name}", 'BENCHMARK');
            }
            
            public function lap(string $label = null): self
            {
                $current = microtime(true);
                $lapTime = ($current - $this->start) * 1000;
                $lapLabel = $label ?? 'Lap ' . (count($this->laps) + 1);
                
                $this->laps[] = [
                    'label' => $lapLabel,
                    'time' => $lapTime,
                    'timestamp' => $current,
                ];
                
                Debug::log("⏱️  {$this->name} - {$lapLabel}: {$lapTime}ms", 'BENCHMARK');
                
                return $this;
            }
            
            public function end(): array
            {
                $end = microtime(true);
                $totalTime = ($end - $this->start) * 1000;
                
                $result = [
                    'name' => $this->name,
                    'total_time' => $totalTime,
                    'laps' => $this->laps,
                    'start' => $this->start,
                    'end' => $end,
                ];
                
                Debug::dump($result, "Benchmark Results: {$this->name}");
                Debug::log("🏆 Finished benchmark: {$this->name} - Total: {$totalTime}ms", 'BENCHMARK');
                
                return $result;
            }
        };
    }

    /**
     * Check if we're in development mode
     */
    public static function isDev(): bool
    {
        return (getenv('APP_ENV') === 'dev') || (getenv('APP_DEBUG') === 'true');
    }

    /**
     * Only execute callback in development mode
     */
    public static function dev(callable $callback): mixed
    {
        if (self::isDev()) {
            return $callback();
        }
        
        return null;
    }

    /**
     * Format bytes to human readable format
     */
    private static function formatBytes(int $bytes): string
    {
        if ($bytes === 0) {
            return '0 B';
        }
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $base = log($bytes, 1024);
        return round(1024 ** ($base - floor($base)), 2) . ' ' . $units[floor($base)];
    }

    /**
     * Clean server array for display (remove sensitive data)
     */
    private static function cleanServerArray(array $server): array
    {
        $sensitive = [
            'HTTP_AUTHORIZATION',
            'HTTP_COOKIE',
            'PHP_AUTH_PW',
            'AUTH_PASSWORD',
        ];
        
        $cleaned = $server;
        foreach ($sensitive as $key) {
            if (isset($cleaned[$key])) {
                $cleaned[$key] = '[HIDDEN]';
            }
        }
        
        return $cleaned;
    }
}
