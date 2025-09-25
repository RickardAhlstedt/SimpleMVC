<?php

namespace SimpleMVC\Debug\Middleware;

use SimpleMVC\Debug\DebugToolbar;
use SimpleMVC\Middleware\MiddlewareInterface;
use SimpleMVC\Core\HTTP\RequestStack;
use SimpleMVC\Core\HTTP\Response;

class DebugToolbarMiddleware implements MiddlewareInterface
{
    private DebugToolbar $toolbar;

    public function __construct(DebugToolbar $toolbar)
    {
        $this->toolbar = $toolbar;
    }

    public function process(RequestStack $request, callable $next): Response
    {
        $response = $next($request);

        // Let's check if we are on the profiler-pages
        if (strpos($request->getPath(), '/debug/') !== false) {
            return $response;
        }

        if (!$this->shouldInjectToolbar($response)) {
            return $response;
        }

        $requestId = uniqid();
        $data = $this->toolbar->collect($requestId);

        $toolbarHtml = $this->generateToolbarHtml($data, $requestId);
        $content = $response->getContent();

        // Inject before closing body tag
        $content = str_replace('</body>', $toolbarHtml . '</body>', $content);
        $response->setContent($content);

        return $response;
    }

    private function shouldInjectToolbar(Response $response): bool
    {
        $contentType = $response->getHeader('Content-Type') ?? '';
        $content = $response->getContent();
    
        // More lenient check - inject if:
        // 1. Content-Type is text/html, OR
        // 2. Content-Type is empty but content contains HTML tags, OR  
        // 3. Content contains </body> tag (likely HTML)
        return (
            strpos($contentType, 'text/html') !== false ||
            (empty($contentType) && (strpos($content, '<html') !== false || strpos($content, '<!DOCTYPE') !== false)) ||
            strpos($content, '</body>') !== false
        );
    }

    private function generateToolbarHtml(array $data, string $requestId): string
    {
        $collectors = $data['collectors'] ?? [];
        
        $html = '<div id="simplemvc-debug-toolbar" style="position: fixed; bottom: 0; left: 0; right: 0; background: #1a1a1a; color: #fff; z-index: 99999; font-family: monospace; font-size: 12px; border-top: 2px solid #007bff;">';
        $html .= '<div style="display: flex; align-items: center; padding: 8px 16px; gap: 16px;">';
        
        // SimpleMVC Logo/Name
        $html .= '<div style="font-weight: bold; color: #007bff;">SimpleMVC</div>';
        
        // Collectors
        foreach ($collectors as $name => $collectorData) {
            $collector = $this->toolbar->getCollector($name);
            if ($collector) {
                $html .= '<div style="cursor: pointer;" onclick="window.open(\'/debug/' . $requestId . '/' . $name . '\', \'_blank\')">';
                $html .= $collector->getIcon() . ' ' . $collector->getLabel();
                $html .= '</div>';
            }
        }
        
        // Close/Minimize button
        $html .= '<div style="margin-left: auto; cursor: pointer;" onclick="document.getElementById(\'simplemvc-debug-toolbar\').style.display=\'none\'">❌</div>';
        
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
}
