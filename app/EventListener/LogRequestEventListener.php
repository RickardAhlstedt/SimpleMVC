<?php

declare(strict_types=1);

namespace App\EventListener;

use SimpleMVC\Event\Event;
use SimpleMVC\Service\LoggerService;

class LogRequestEventListener
{
    private LoggerService $logger;

    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
    }
    
    public static function getSubscribedEvents(): array
    {
        return [
            'application.route_matched' => 'onRequest',
        ];
    }

    public function onRequest(Event $event): void
    {
        $data = $event->getData();
        $route = isset($data['route']) ? $data['route'] : 'unknown';
        $route = is_string($route) ? $route : var_export($route, true);
        $this->logger->info("Incoming request to route: {$route}");
    }
}
