<?php

declare(strict_types=1);

namespace SimpleMVC\Event;

class Event implements EventInterface
{
    private string $name;
    private array $data;

    public function __construct(string $name, array $data = [])
    {
        $this->name = $name;
        $this->data = $data;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function stopPropagation(): void
    {
        // Implementation can be added if needed
    }

    public function isPropagationStopped(): bool
    {
        return false; // Implementation can be added if needed
    }
}
