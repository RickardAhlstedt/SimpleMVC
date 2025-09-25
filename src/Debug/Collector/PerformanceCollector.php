<?php

namespace SimpleMVC\Debug\Collector;

use SimpleMVC\Debug\DataCollectorInterface;

class PerformanceCollector implements DataCollectorInterface
{
    private float $startTime;
    private int $startMemory;

    public function __construct()
    {
        $this->startTime = $_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true);
        $this->startMemory = memory_get_usage(true);
    }

    public function getName(): string
    {
        return 'performance';
    }

    public function getIcon(): string
    {
        return '⚡';
    }

    public function getLabel(): string
    {
        return 'Performance';
    }

    public function getPriority(): int
    {
        return 80;
    }

    public function collect(): array
    {
        return [
            'execution_time' => microtime(true) - $this->startTime,
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'memory_start' => $this->startMemory,
            'memory_diff' => memory_get_usage(true) - $this->startMemory,
        ];
    }
}