<?php

declare(strict_types=1);

namespace SimpleMVC\Event;

interface EventListenerInterface
{
    public static function getSubscribedEvents(): array;
}
