<?php

namespace SimpleMVC\Debug\Collector;

use SimpleMVC\Debug\DataCollectorInterface;

class DatabaseCollector implements DataCollectorInterface
{
    private static array $queries = [];

    public static function logQuery(string $sql, array $params = [], float $time = 0): void
    {
        self::$queries[] = [
            'sql' => $sql,
            'params' => $params,
            'time' => $time,
            'backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10)
        ];
    }

    public function getName(): string
    {
        return 'database';
    }

    public function getIcon(): string
    {
        return '🗄️';
    }

    public function getLabel(): string
    {
        return 'Database (' . count(self::$queries) . ')';
    }

    public function getPriority(): int
    {
        return 90;
    }

    public function collect(): array
    {
        return [
            'query_count' => count(self::$queries),
            'total_time' => array_sum(array_column(self::$queries, 'time')),
            'queries' => self::$queries,
        ];
    }
}