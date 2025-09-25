<?php

namespace SimpleMVC\Debug\Collector;

use SimpleMVC\Debug\DataCollectorInterface;

class TwigCollector implements DataCollectorInterface
{
    private static array $templates = [];
    private static array $renderStack = [];
    private static float $totalRenderTime = 0;

    public static function startRender(string $template, array $context = []): void
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
        
        $renderData = [
            'template' => $template,
            'start_time' => microtime(true),
            'start_memory' => memory_get_usage(true),
            'context_size' => count($context),
            'context_keys' => array_keys($context),
            'backtrace' => self::formatBacktrace($backtrace),
            'parent' => end(self::$renderStack) ?: null,
            'children' => [],
            'depth' => count(self::$renderStack),
        ];

        // Add context data (limited for performance)
        $renderData['context_preview'] = self::limitContextForStorage($context);

        // Track parent-child relationships
        if (!empty(self::$renderStack)) {
            $parentIndex = array_key_last(self::$renderStack);
            if (isset(self::$templates[$parentIndex])) {
                self::$templates[$parentIndex]['children'][] = count(self::$templates);
            }
        }

        self::$renderStack[] = count(self::$templates);
        self::$templates[] = $renderData;
    }

    public static function endRender(string $template): void
    {
        if (empty(self::$renderStack)) {
            return;
        }

        $renderIndex = array_pop(self::$renderStack);
        
        if (!isset(self::$templates[$renderIndex])) {
            return;
        }

        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        
        $renderTime = $endTime - self::$templates[$renderIndex]['start_time'];
        $memoryUsed = $endMemory - self::$templates[$renderIndex]['start_memory'];
        
        self::$templates[$renderIndex]['end_time'] = $endTime;
        self::$templates[$renderIndex]['end_memory'] = $endMemory;
        self::$templates[$renderIndex]['render_time'] = $renderTime;
        self::$templates[$renderIndex]['memory_used'] = $memoryUsed;
        
        self::$totalRenderTime += $renderTime;
    }

    public static function addTemplateInfo(string $template, array $info): void
    {
        // Find the most recent template render for this template
        for ($i = count(self::$templates) - 1; $i >= 0; $i--) {
            if (self::$templates[$i]['template'] === $template) {
                self::$templates[$i]['info'] = array_merge(
                    self::$templates[$i]['info'] ?? [],
                    $info
                );
                break;
            }
        }
    }

    public static function addError(string $template, \Throwable $error): void
    {
        // Find the template and add error info
        for ($i = count(self::$templates) - 1; $i >= 0; $i--) {
            if (self::$templates[$i]['template'] === $template) {
                self::$templates[$i]['error'] = [
                    'message' => $error->getMessage(),
                    'file' => $error->getFile(),
                    'line' => $error->getLine(),
                    'code' => $error->getCode(),
                ];
                break;
            }
        }
    }

    public function getName(): string
    {
        return 'twig';
    }

    public function getIcon(): string
    {
        return '🎨';
    }

    public function getLabel(): string
    {
        $count = count(self::$templates);
        $time = round(self::$totalRenderTime * 1000, 2);
        return "Twig ({$count}) {$time}ms";
    }

    public function getPriority(): int
    {
        return 60;
    }

    public function collect(): array
    {
        return [
            'template_count' => count(self::$templates),
            'total_render_time' => self::$totalRenderTime,
            'templates' => self::$templates,
            'template_tree' => self::buildTemplateTree(),
            'most_used_templates' => self::getMostUsedTemplates(),
            'slowest_templates' => self::getSlowestTemplates(),
        ];
    }

    private static function limitContextForStorage(array $context, int $maxDepth = 2, int $maxItems = 10): array
    {
        if ($maxDepth <= 0) {
            return ['[MAX_DEPTH_REACHED]'];
        }

        $limited = [];
        $count = 0;

        foreach ($context as $key => $value) {
            if ($count >= $maxItems) {
                $limited['[...]'] = count($context) - $maxItems . ' more items';
                break;
            }

            if (is_array($value)) {
                $limited[$key] = self::limitContextForStorage($value, $maxDepth - 1, $maxItems);
            } elseif (is_object($value)) {
                $limited[$key] = [
                    '[OBJECT]' => get_class($value),
                    '[ID]' => method_exists($value, 'getId') ? $value->getId() : 'N/A',
                ];
            } elseif (is_string($value) && strlen($value) > 100) {
                $limited[$key] = substr($value, 0, 100) . '... (' . strlen($value) . ' chars)';
            } else {
                $limited[$key] = $value;
            }
            
            $count++;
        }

        return $limited;
    }

    private static function formatBacktrace(array $backtrace): array
    {
        $formatted = [];
        
        foreach (array_slice($backtrace, 0, 5) as $trace) {
            if (!isset($trace['file']) || strpos($trace['file'], 'Twig') !== false) {
                continue;
            }
            
            $formatted[] = [
                'file' => basename($trace['file']),
                'line' => $trace['line'] ?? '?',
                'function' => $trace['function'] ?? 'unknown',
                'class' => $trace['class'] ?? null,
            ];
        }
        
        return $formatted;
    }

    private static function buildTemplateTree(): array
    {
        $tree = [];
        
        foreach (self::$templates as $index => $template) {
            if ($template['depth'] === 0) { // Root template
                $tree[] = self::buildTemplateNode($index);
            }
        }
        
        return $tree;
    }

    private static function buildTemplateNode(int $index): array
    {
        $template = self::$templates[$index];
        $node = [
            'index' => $index,
            'template' => $template['template'],
            'render_time' => $template['render_time'] ?? 0,
            'memory_used' => $template['memory_used'] ?? 0,
            'children' => [],
        ];
        
        foreach ($template['children'] as $childIndex) {
            $node['children'][] = self::buildTemplateNode($childIndex);
        }
        
        return $node;
    }

    private static function getMostUsedTemplates(): array
    {
        $usage = [];
        
        foreach (self::$templates as $template) {
            $name = $template['template'];
            if (!isset($usage[$name])) {
                $usage[$name] = 0;
            }
            $usage[$name]++;
        }
        
        arsort($usage);
        return array_slice($usage, 0, 10, true);
    }

    private static function getSlowestTemplates(): array
    {
        $templates = self::$templates;
        
        // Filter out templates without render time
        $templates = array_filter($templates, fn($t) => isset($t['render_time']));
        
        // Sort by render time
        usort($templates, fn($a, $b) => ($b['render_time'] ?? 0) <=> ($a['render_time'] ?? 0));
        
        return array_slice($templates, 0, 10);
    }

    public static function clear(): void
    {
        self::$templates = [];
        self::$renderStack = [];
        self::$totalRenderTime = 0;
    }
}