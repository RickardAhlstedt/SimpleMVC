<?php

namespace SimpleMVC\Debug\Controller;

use SimpleMVC\Core\HTTP\AbstractController;
use SimpleMVC\Attribute\Route;
use SimpleMVC\Attribute\Controller;
use SimpleMVC\Core\HTTP\Response;

#[Controller]
class DebugController extends AbstractController
{
    #[Route(name: 'debug_profiler', path: '/debug/{requestId}', method: 'GET')]
    public function profiler(string $requestId): Response
    {
        return $this->renderProfiler($requestId, null);
    }

    #[Route(name: 'debug_collector', path: '/debug/{requestId}/{collector}', method: 'GET')]
    public function collector(string $requestId, string $collectorName): Response
    {
        return $this->renderProfiler($requestId, $collectorName);
    }

    private function renderProfiler(string $requestId, ?string $collector = null): Response
    {
        $debugToolbar = \SimpleMVC\Core\Container::getInstance()->get(\SimpleMVC\Debug\DebugToolbar::class);

        $profile = $debugToolbar->getProfile($requestId);
        
        if (!$profile) {
            return new Response('Debug data not found', 404);
        }
        
        // Get collector metadata
        $collectors = [];
        foreach ($debugToolbar->getCollectors() as $name => $collectorInstance) {
            $collectors[$name] = [
                'icon' => $collectorInstance->getIcon(),
                'label' => $collectorInstance->getLabel(),
                'priority' => $collectorInstance->getPriority(),
            ];
        }
        
        $templateData = [
            'data' => $profile,
            'requestId' => $requestId,
            'currentCollector' => $collector,
            'collectors' => $collectors,
        ];
        
        // If we have a specific collector, render its template with that collector's data
        if ($collector && isset($profile['collectors'][$collector])) {
            $templateData['data'] = $profile['collectors'][$collector];
            return $this->render("debug/partials/{$collector}.html.twig", $templateData);
        }
        
        // Otherwise render the main profiler page
        return $this->render('debug/profiler.html.twig', $templateData);
    }
}