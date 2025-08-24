<?php

declare(strict_types=1);

namespace SimpleMVC\Event;

interface EventInterface
{
    public function getName(): string;
    public function getData(): array;
    public function stopPropagation(): void;
    public function isPropagationStopped(): bool;
}