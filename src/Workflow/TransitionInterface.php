<?php

namespace SimpleMVC\Workflow;

interface TransitionInterface
{
    public function getName(): string;
    public function getFrom(): array;
    public function getTo(): string;
    public function getGuards(): array;
    public function getActions(): array;
}