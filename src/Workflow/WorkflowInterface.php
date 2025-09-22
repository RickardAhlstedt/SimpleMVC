<?php

namespace SimpleMVC\Workflow;

interface WorkflowInterface
{
    public function getName(): string;
    public function getStates(): array;
    public function getTransitions(): array;
    public function getInitialState(): string;
}