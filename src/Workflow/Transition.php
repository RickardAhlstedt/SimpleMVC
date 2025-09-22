<?php

namespace SimpleMVC\Workflow;

class Transition implements TransitionInterface
{
    public function __construct(
        private string $name,
        private array $from,
        private string $to,
        private array $guards = [],
        private array $actions = []
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getFrom(): array
    {
        return $this->from;
    }

    public function getTo(): string
    {
        return $this->to;
    }

    public function getGuards(): array
    {
        return $this->guards;
    }

    public function getActions(): array
    {
        return $this->actions;
    }
}