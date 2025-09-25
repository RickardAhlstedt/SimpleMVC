<?php

namespace SimpleMVC\Debug\Collector;

use SimpleMVC\Debug\DataCollectorInterface;

class DumpCollector implements DataCollectorInterface
{
    private static array $dumps = [];

    public static function dump($var, string $label = null, array $backtrace = null): void
    {
        $backtrace = $backtrace ?? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
        
        self::$dumps[] = [
            'data' => $var,
            'label' => $label,
            'type' => gettype($var),
            'file' => $backtrace[0]['file'] ?? 'unknown',
            'line' => $backtrace[0]['line'] ?? 0,
            'function' => $backtrace[1]['function'] ?? 'unknown',
            'class' => $backtrace[1]['class'] ?? null,
            'timestamp' => microtime(true),
            'serialized' => self::serializeForStorage($var),
        ];
    }

    public function getName(): string
    {
        return 'dump';
    }

    public function getIcon(): string
    {
        return '🪣';
    }

    public function getLabel(): string
    {
        $count = count(self::$dumps);
        return "Dumps ({$count})";
    }

    public function getPriority(): int
    {
        return 70;
    }

    public function collect(): array
    {
        return [
            'dump_count' => count(self::$dumps),
            'dumps' => self::$dumps,
        ];
    }

    private static function serializeForStorage($var): array
    {
        if (is_object($var)) {
            return [
                'type' => 'object',
                'class' => get_class($var),
                'properties' => self::getObjectProperties($var),
            ];
        }

        if (is_array($var)) {
            $serializedData = [];
            $count = 0;
            foreach ($var as $key => $value) {
                if ($count >= 50) break; // Limit to 50 items for performance
                $serializedData[$key] = self::serializeForStorage($value);
                $count++;
            }
            
            return [
                'type' => 'array',
                'count' => count($var),
                'data' => $serializedData,
            ];
        }

        if (is_resource($var)) {
            return [
                'type' => 'resource',
                'resource_type' => get_resource_type($var),
                'value' => (string)$var,
            ];
        }

        if (is_string($var)) {
            return [
                'type' => 'string',
                'value' => strlen($var) > 1000 ? substr($var, 0, 1000) . '...' : $var,
                'length' => strlen($var),
            ];
        }

        return [
            'type' => gettype($var),
            'value' => $var,
        ];
    }

    private static function getObjectProperties($obj): array
    {
        try {
            $reflection = new \ReflectionClass($obj);
            $properties = [];

            foreach ($reflection->getProperties() as $property) {
                if (count($properties) >= 20) break; // Limit properties for performance
                
                $property->setAccessible(true);
                $name = $property->getName();
                $visibility = $property->isPrivate() ? 'private' : ($property->isProtected() ? 'protected' : 'public');
                
                try {
                    $value = $property->getValue($obj);
                    $properties[$name] = [
                        'visibility' => $visibility,
                        'value' => self::serializeForStorage($value),
                    ];
                } catch (\Throwable $e) {
                    $properties[$name] = [
                        'visibility' => $visibility,
                        'value' => ['type' => 'error', 'value' => 'Error: ' . $e->getMessage()],
                    ];
                }
            }

            return $properties;
        } catch (\Throwable $e) {
            return ['error' => 'Could not serialize object: ' . $e->getMessage()];
        }
    }

    public static function clear(): void
    {
        self::$dumps = [];
    }
}