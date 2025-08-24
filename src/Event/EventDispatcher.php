<?php

declare(strict_types=1);

namespace SimpleMVC\Event;

class EventDispatcher
{
    private array $listeners = [];

    public function addListener(string $eventName, callable $listener, int $priority = 0): void
    {
        $this->listeners[$eventName][$priority][] = $listener;
        krsort($this->listeners[$eventName]); // Higher priority first
    }

    public function dispatch(EventInterface $event): void
    {
        $name = $event->getName();
        if (!isset($this->listeners[$name])) {
            return;
        }

        foreach ($this->listeners[$name] as $priority => $listeners) {
            foreach ($listeners as $listener) {
                $listener($event);
                if ($event->isPropagationStopped()) {
                    return;
                }
            }
        }
    }
}